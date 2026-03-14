<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * Obtener el modelo específico del repositorio
     */
    abstract protected function getModel(): Model;

    /**
     * Obtener todos los registros
     */
    public function all($columns = ['*']): Collection
    {
        return $this->model->select($columns)->get();
    }

    /**
     * Buscar por ID
     */
    public function find($id, $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->find($id);
    }

    /**
     * Buscar por ID o fallar
     */
    public function findOrFail($id, $columns = ['*']): Model
    {
        return $this->model->select($columns)->findOrFail($id);
    }

    /**
     * Buscar por criterios específicos
     */
    public function findBy(string $field, $value, $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->where($field, $value)->first();
    }

    /**
     * Buscar todos los que cumplen criterios
     */
    public function findAllBy(string $field, $value, $columns = ['*']): Collection
    {
        return $this->model->select($columns)->where($field, $value)->get();
    }

    /**
     * Crear nuevo registro
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Actualizar registro
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Actualizar por ID
     */
    public function updateById($id, array $data): Model
    {
        $model = $this->findOrFail($id);
        return $this->update($model, $data);
    }

    /**
     * Eliminar registro
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Eliminar por ID
     */
    public function deleteById($id): bool
    {
        $model = $this->findOrFail($id);
        return $this->delete($model);
    }

    /**
     * Paginación
     */
    public function paginate($perPage = 15, $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->select($columns)->paginate($perPage);
    }

    /**
     * Contar registros
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Contar por criterios
     */
    public function countBy(string $field, $value): int
    {
        return $this->model->where($field, $value)->count();
    }

    /**
     * Crear o actualizar
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Obtener registros con relaciones
     */
    public function with($relations, $columns = ['*']): Collection
    {
        return $this->model->with($relations)->select($columns)->get();
    }

    /**
     * Obtener modelo fresco
     */
    public function fresh(Model $model): Model
    {
        return $model->fresh();
    }
}