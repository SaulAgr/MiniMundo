// Configuración base para las peticiones CRUD
const API_BASE = '../crud/';
const API = {
    PADRE: API_BASE + 'crudPadre.php',
    ALUMNO: API_BASE + 'crudAlumno.php',
    PROFESOR: API_BASE + 'crudProfesor.php',
    COORDINADOR: API_BASE + 'crudCoordinador.php',
    DIRECTOR: API_BASE + 'crudDirector.php'
};

// Función para manejar errores comunes de las peticiones fetch
async function handleApiResponse(response) {
    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Error en la petición');
    }
    return response.json();
}

async function safeFetch(url, options = {}) {
  try {
    const response = await fetch(url, {
      ...options,
      headers: {
        'Accept': 'application/json',
        ...options.headers
      }
    });
    
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    
    const contentType = response.headers.get('content-type');
    if (!contentType?.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Expected JSON, got ${contentType}: ${text.substring(0, 100)}`);
    }
    
    return response.json();
  } catch (error) {
    console.error('Fetch error:', error);
    throw error;
  }
}