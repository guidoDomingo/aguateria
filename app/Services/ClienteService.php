<?php

namespace App\Services;

use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use App\Models\HistorialCliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteService
{
    protected $clienteRepository;

    public function __construct(ClienteRepository $clienteRepository)
    {
        $this->clienteRepository = $clienteRepository;
    }

    /**
     * Crear nuevo cliente
     */
    public function crear(array $datos): array
    {
        try {
            return DB::transaction(function () use ($datos) {
                // Generar código automático si no se proporciona
                if (empty($datos['codigo_cliente'])) {
                    $datos['codigo_cliente'] = $this->clienteRepository->siguienteCodigoCliente();
                }

                // Establecer fecha de alta si no se proporciona
                if (empty($datos['fecha_alta'])) {
                    $datos['fecha_alta'] = now();
                }

                $cliente = $this->clienteRepository->create($datos);

                // Registrar en historial
                $this->registrarHistorial($cliente, 'creacion', null, null, 'Cliente creado');

                return [
                    'success' => true,
                    'message' => "Cliente {$cliente->codigo_cliente} - {$cliente->nombre} {$cliente->apellido} creado exitosamente",
                    'cliente' => $cliente
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar cliente por ID
     */
    public function actualizarPorId(int $clienteId, array $datos): array
    {
        try {
            return DB::transaction(function () use ($clienteId, $datos) {
                $cliente = $this->clienteRepository->findOrFail($clienteId);
                $resultado = $this->actualizar($cliente, $datos);

                return [
                    'success' => true,
                    'message' => "Cliente {$resultado->codigo_cliente} - {$resultado->nombre} {$resultado->apellido} actualizado exitosamente",
                    'cliente' => $resultado
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar cliente
     */
    public function actualizar(Cliente $cliente, array $datos): Cliente
    {
        return DB::transaction(function () use ($cliente, $datos) {
            $datosOriginales = $cliente->toArray();
            
            $clienteActualizado = $this->clienteRepository->update($cliente, $datos);

            // Registrar cambios significativos en el historial
            $this->registrarCambiosHistorial($clienteActualizado, $datosOriginales, $datos);

            return $clienteActualizado;
        });
    }

    /**
     * Suspender cliente
     */
    public function suspender(Cliente $cliente, string $motivo = null): Cliente
    {
        return DB::transaction(function () use ($cliente, $motivo) {
            $estadoAnterior = $cliente->estado;
            
            $cliente = $this->clienteRepository->update($cliente, ['estado' => 'suspendido']);

            $this->registrarHistorial(
                $cliente, 
                'suspension', 
                $estadoAnterior, 
                'suspendido',
                $motivo ?? 'Cliente suspendido'
            );

            return $cliente;
        });
    }

    /**
     * Activar cliente
     */
    public function activar(Cliente $cliente): Cliente
    {
        return DB::transaction(function () use ($cliente) {
            $estadoAnterior = $cliente->estado;
            
            $cliente = $this->clienteRepository->update($cliente, ['estado' => 'activo']);

            $this->registrarHistorial(
                $cliente, 
                'activacion', 
                $estadoAnterior, 
                'activo',
                'Cliente reactivado'
            );

            return $cliente;
        });
    }

    /**
     * Dar de baja cliente
     */
    public function darDeBaja(Cliente $cliente, string $motivo): Cliente
    {
        return DB::transaction(function () use ($cliente, $motivo) {
            $cliente = $this->clienteRepository->update($cliente, [
                'estado' => 'retirado',
                'fecha_baja' => now(),
                'motivo_baja' => $motivo
            ]);

            $this->registrarHistorial(
                $cliente, 
                'baja', 
                null, 
                null,
                $motivo
            );

            return $cliente;
        });
    }

    /**
     * Cambiar tarifa del cliente
     */
    public function cambiarTarifa(Cliente $cliente, int $nuevaTarifaId, string $motivo = null): Cliente
    {
        return DB::transaction(function () use ($cliente, $nuevaTarifaId, $motivo) {
            $tarifaAnterior = $cliente->tarifa_id;
            
            $cliente = $this->clienteRepository->update($cliente, ['tarifa_id' => $nuevaTarifaId]);

            $this->registrarHistorial(
                $cliente, 
                'cambio_tarifa', 
                $tarifaAnterior, 
                $nuevaTarifaId,
                $motivo ?? 'Cambio de tarifa'
            );

            return $cliente;
        });
    }

    /**
     * Asignar cobrador
     */
    public function asignarCobrador(Cliente $cliente, int $cobradorId): Cliente
    {
        return DB::transaction(function () use ($cliente, $cobradorId) {
            $cobradorAnterior = $cliente->cobrador_id;
            
            $cliente = $this->clienteRepository->update($cliente, ['cobrador_id' => $cobradorId]);

            $this->registrarHistorial(
                $cliente, 
                'cambio_cobrador', 
                $cobradorAnterior, 
                $cobradorId,
                'Cobrador asignado/cambiado'
            );

            return $cliente;
        });
    }

    /**
     * Obtener estadísticas de clientes
     */
    public function estadisticas(): array
    {
        return $this->clienteRepository->estadisticas();
    }

    /**
     * Buscar clientes
     */
    public function buscar(string $termino, int $perPage = 15)
    {
        return $this->clienteRepository->buscar($termino, $perPage);
    }

    /**
     * Registrar evento en historial
     */
    private function registrarHistorial(
        Cliente $cliente, 
        string $tipoEvento, 
        $valorAnterior = null, 
        $valorNuevo = null, 
        string $motivo = null
    ): void {
        HistorialCliente::create([
            'cliente_id' => $cliente->id,
            'usuario_id' => Auth::id(),
            'tipo_evento' => $tipoEvento,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'motivo' => $motivo,
            'datos_completos' => $tipoEvento === 'creacion' ? $cliente->toArray() : null,
        ]);
    }

    /**
     * Eliminar cliente
     */
    public function eliminar(int $clienteId): array
    {
        try {
            return DB::transaction(function () use ($clienteId) {
                $cliente = $this->clienteRepository->findOrFail($clienteId);

                // Verificar si tiene relaciones que impidan la eliminación
                $restricciones = $this->verificarRestriccionesEliminacion($cliente);
                
                if (!empty($restricciones)) {
                    return [
                        'success' => false,
                        'message' => 'No se puede eliminar el cliente porque tiene: ' . implode(', ', $restricciones),
                        'restricciones' => $restricciones
                    ];
                }

                // Registrar en historial antes de eliminar
                $this->registrarHistorial(
                    $cliente, 
                    'baja', 
                    null, 
                    null,
                    'Cliente eliminado del sistema'
                );

                // Eliminar cliente
                $nombreCompleto = trim($cliente->nombre . ' ' . $cliente->apellido);
                $codigoCliente = $cliente->codigo_cliente;
                
                $eliminado = $this->clienteRepository->delete($cliente);

                if ($eliminado) {
                    return [
                        'success' => true,
                        'message' => "Cliente {$codigoCliente} - {$nombreCompleto} eliminado exitosamente"
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al eliminar el cliente'
                    ];
                }
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar restricciones para eliminación
     */
    private function verificarRestriccionesEliminacion(Cliente $cliente): array
    {
        $restricciones = [];

        try {
            // Verificar facturas
            if ($cliente->facturas()->count() > 0) {
                $restricciones[] = 'facturas registradas';
            }
        } catch (\Exception $e) {
            // Si hay error con facturas, no bloquear eliminación
        }

        try {
            // Verificar pagos
            if ($cliente->pagos()->count() > 0) {
                $restricciones[] = 'pagos registrados';
            }
        } catch (\Exception $e) {
            // Si hay error con pagos, no bloquear eliminación
        }

        try {
            // Verificar cortes de servicio
            if ($cliente->cortesServicio()->count() > 0) {
                $restricciones[] = 'cortes de servicio';
            }
        } catch (\Exception $e) {
            // Si hay error con cortes, no bloquear eliminación
        }

        try {
            // Verificar reconexiones
            if ($cliente->reconexiones()->count() > 0) {
                $restricciones[] = 'reconexiones registradas';
            }
        } catch (\Exception $e) {
            // Si hay error con reconexiones, no bloquear eliminación
        }

        return $restricciones;
    }

    /**
     * Registrar cambios en historial
     */
    private function registrarCambiosHistorial(Cliente $cliente, array $datosOriginales, array $datosNuevos): void
    {
        $camposImportantes = ['nombre', 'apellido', 'telefono', 'direccion', 'barrio_id', 'tarifa_id', 'cobrador_id'];
        
        foreach ($camposImportantes as $campo) {
            if (isset($datosNuevos[$campo]) && $datosOriginales[$campo] != $datosNuevos[$campo]) {
                $this->registrarHistorial(
                    $cliente,
                    'modificacion',
                    $datosOriginales[$campo],
                    $datosNuevos[$campo],
                    "Campo '{$campo}' modificado"
                );
            }
        }
    }
}