<svg {{ $attributes }} viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
    <!-- Fondo circular -->
    <circle cx="50" cy="50" r="48" fill="url(#gradient)" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
    
    <!-- Gradiente -->
    <defs>
        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Gota de agua principal -->
    <path d="M50 20 C40 30, 40 40, 50 50 C60 40, 60 30, 50 20 Z" fill="rgba(255,255,255,0.9)"/>
    
    <!-- Ondas de agua -->
    <path d="M20 55 Q30 50, 40 55 T60 55" stroke="rgba(255,255,255,0.7)" stroke-width="2" fill="none"/>
    <path d="M25 65 Q35 60, 45 65 T65 65" stroke="rgba(255,255,255,0.5)" stroke-width="1.5" fill="none"/>
    <path d="M30 75 Q40 70, 50 75 T70 75" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
    
    <!-- Puntos decorativos -->
    <circle cx="35" cy="35" r="2" fill="rgba(255,255,255,0.6)"/>
    <circle cx="65" cy="40" r="1.5" fill="rgba(255,255,255,0.4)"/>
    <circle cx="30" cy="45" r="1" fill="rgba(255,255,255,0.5)"/>
</svg>
