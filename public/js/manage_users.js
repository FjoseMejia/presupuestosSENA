// Función para filtrar usuarios
function filterUsers() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const verifiedFilter = document.getElementById('verified-filter').value;
    const roleFilter = document.getElementById('role-filter').value;
    const rows = document.querySelectorAll('#users-table-body tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const id = row.cells[0].textContent.toLowerCase();
        const email = row.cells[1].textContent.toLowerCase();
        const verifiedBadge = row.cells[2].querySelector('.badge');
        const isVerified = verifiedBadge.classList.contains('bg-success');
        const role = row.cells[3].textContent.toLowerCase();
        
        // Filtro por búsqueda
        const matchesSearch = !searchTerm || 
            email.includes(searchTerm) || 
            id.includes(searchTerm);
        
        // Filtro por verificación
        const matchesVerified = verifiedFilter === 'all' || 
            (verifiedFilter === 'verified' && isVerified) || 
            (verifiedFilter === 'not-verified' && !isVerified);
        
        // Filtro por rol
        const matchesRole = roleFilter === 'all' || 
            role === roleFilter.toLowerCase();
        
        if (matchesSearch && matchesVerified && matchesRole) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Mostrar/ocultar mensaje de no resultados
    const noResults = document.getElementById('no-results');
    if (visibleCount === 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }
    
    // Actualizar información de resultados
    updateResultsInfo(visibleCount);
}

// Función para actualizar la información de resultados
function updateResultsInfo(count) {
    const resultsInfo = document.getElementById('results-info');
    const searchTerm = document.getElementById('search-input').value;
    const verifiedFilter = document.getElementById('verified-filter').value;
    const roleFilter = document.getElementById('role-filter').value;
    
    let message = `Mostrando ${count} usuario${count !== 1 ? 's' : ''}`;
    
    if (searchTerm) {
        message += ` para "${searchTerm}"`;
    }
    
    if (verifiedFilter !== 'all') {
        message += `, estado: ${verifiedFilter === 'verified' ? 'verificados' : 'no verificados'}`;
    }
    
    if (roleFilter !== 'all') {
        message += `, rol: ${roleFilter}`;
    }
    
    resultsInfo.textContent = message;
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar event listeners para búsqueda y filtros
    const searchInput = document.getElementById('search-input');
    const verifiedFilter = document.getElementById('verified-filter');
    const roleFilter = document.getElementById('role-filter');
    
    if (searchInput) searchInput.addEventListener('input', filterUsers);
    if (verifiedFilter) verifiedFilter.addEventListener('change', filterUsers);
    if (roleFilter) roleFilter.addEventListener('change', filterUsers);
    
    // Inicializar contador de resultados
    const initialCount = document.querySelectorAll('#users-table-body tr').length;
    updateResultsInfo(initialCount);
});