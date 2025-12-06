import axios from 'axios';

// Back-end root URL (without trailing /api) and API base URL (/api)
// Example env:
// - REACT_APP_API_BASE_URL=http://127.0.0.1:8000/api
// - or REACT_APP_API_BASE_URL=http://localhost/scheduling management system/Backend/public/api
const apiBase = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api';
const backendRoot = apiBase.endsWith('/api') ? apiBase.slice(0, -4) : apiBase;

// Debug logging
console.log('🔧 Environment Debug:');
console.log('📍 REACT_APP_API_BASE_URL:', process.env.REACT_APP_API_BASE_URL);
console.log('📍 apiBase:', apiBase);
console.log('📍 backendRoot:', backendRoot);


// Axios instances
const API = axios.create({
  baseURL: apiBase,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // Don't add custom transformRequest - let axios handle it by default
  // This ensures proper JSON serialization without conflicts
});
const AuthAPI = axios.create({
  baseURL: backendRoot,
  withCredentials: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
});

// Response interceptor to handle password change requirements
API.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 403 && error.response?.data?.must_change_password) {
      if (!window.location.pathname.includes('change-password')) {
        alert('You must change your password before continuing.');
      }
    }
    return Promise.reject(error);
  }
);

// Token-based authentication
export async function login({ login, password }) {
  // Use Laravel backend directly - ensure we're using the correct URL
  const backendUrl = process.env.REACT_APP_API_BASE_URL 
    ? process.env.REACT_APP_API_BASE_URL.replace(/\/api$/, '')
    : 'http://localhost:8000';
  const url = `${backendUrl}/api/login`;
  console.log('🔗 Login URL:', url);
  console.log('🔗 backendRoot calculated:', backendRoot);
  console.log('🔗 backendUrl used:', backendUrl);

  const response = await axios.post(url, { login, password });

  if (response.data?.access_token) {
    const token = response.data.access_token;
    localStorage.setItem('token', token);

    if (response.data.user) {
      localStorage.setItem('userInfo', JSON.stringify(response.data.user));
    }

    API.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    throw new Error('Login response missing access_token');
  }

  return response;
}

export async function logout() {
  const token = localStorage.getItem('token');
  if (token) {
    try {
      await AuthAPI.post('/logout-web');
    } catch (err) {
      console.error('Logout error:', err);
    }
  }
  
  localStorage.removeItem('token');
  localStorage.removeItem('userInfo');
  delete API.defaults.headers.common['Authorization'];
}

// Legacy session-based functions (for compatibility)
export async function loginWeb({ login: username, password }) {
  console.log('🔐 loginWeb called with:', { username, password: '***' });
  console.log('📡 API base URL:', API.defaults.baseURL);
  
  try {
    const result = await login({ login: username, password }); // Redirect to token-based login
    console.log('✅ loginWeb success:', result.data);
    return result;
  } catch (error) {
    console.error('❌ loginWeb error:', error);
    console.error('Error details:', {
      message: error.message,
      code: error.code,
      status: error.response?.status,
      data: error.response?.data,
      config: error.config
    });
    throw error;
  }
}

export async function logoutWeb() {
  return logout(); // Redirect to token-based logout
}

// Initialize token from localStorage on app start
const token = localStorage.getItem('token');
if (token) {
  API.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  console.log('🔑 Token initialized from localStorage:', token.substring(0, 20) + '...');
} else {
  console.log('⚠️ No token found in localStorage');
}

// Override API calls to use the unified dashboard endpoint
const originalGet = API.get;
const originalPost = API.post;
const originalDelete = API.delete;

API.get = function(url, config = {}) {
  console.log('🚀 API.get called with URL:', url);
  console.log('🚀 Base URL:', this.defaults.baseURL);
  
  // Use proper Laravel API routes (no query parameter conversion needed)
  if (url === '/user') {
    console.log('🔄 Routing /user to /api/user (Laravel route)');
    // Ensure Authorization header is included
    const headers = {
      ...this.defaults.headers.common,
      ...(config.headers || {})
    };
    return originalGet.call(this, '/user', { ...config, headers });
  } else if (url === '/tasks') {
    console.log('🔄 Routing /tasks to ?endpoint=tasks');
    return originalGet.call(this, '?endpoint=tasks', config);
  } else if (url === '/users') {
    console.log('🔧 Users API call:', url, '→ Laravel API');
    return originalGet.call(this, '/users', config);
  } else if (url === '/statistics') {
    console.log('🔄 Routing /statistics to ?endpoint=statistics');
    return originalGet.call(this, '?endpoint=statistics', config);
  } else if (url === '/ict-dashboard') {
    console.log('🔧 ICT Dashboard API call:', url, '→ REAL IT ASSETS API');
    return axios.get('http://localhost/real-it-assets-api.php?endpoint=ict-dashboard', config);
  } else if (url === '/it-assets/statistics') {
    console.log('🔧 IT Assets Statistics API call:', url, '→ REAL IT ASSETS API');
    return axios.get('http://localhost/real-it-assets-api.php?endpoint=it-assets/statistics', config);
  } else if (url.startsWith('/it-assets')) {
    // Handle individual asset requests like /it-assets/123
    const assetIdMatch = url.match(/^\/it-assets\/(\d+)$/);
    if (assetIdMatch) {
      const assetId = assetIdMatch[1];
      console.log('🔧 Individual IT Asset API call:', url, '→ REAL IT ASSETS API (it-asset endpoint)');
      return axios.get(`http://localhost/real-it-assets-api.php?endpoint=it-asset&id=${assetId}`, config);
    }
    
    // Use real IT assets API for all other IT assets calls
    const urlParams = url.includes('?') ? '&' + url.split('?')[1] : '';
    const endpoint = url === '/it-assets/statistics' ? 'it-assets/statistics' : 'it-assets';
    console.log('🔧 IT Assets API call:', url, '→ REAL IT ASSETS API');
    return axios.get(`http://localhost/real-it-assets-api.php?endpoint=${endpoint}${urlParams}`, config);
  } else if (url.startsWith('/audit-logs')) {
    // Handle audit logs with query parameters - use real audit logs API
    const urlParams = url.includes('?') ? '?' + url.split('?')[1] : '';
    const isStatistics = url.includes('/statistics');
    const apiUrl = isStatistics ? 
      `http://localhost/audit-logs-api.php?statistics=1${urlParams.replace('?', '&')}` :
      `http://localhost/audit-logs-api.php${urlParams}`;
    console.log('🔧 Audit Logs API call:', url, '→ REAL AUDIT LOGS API', isStatistics ? '(statistics)' : '');
    return axios.get(apiUrl, config);
  } else if (url.startsWith('/demand-notes')) {
    // Handle demand notes with query parameters
    const urlParams = url.includes('?') ? '&' + url.split('?')[1] : '';
    console.log('🔧 Demand Notes API call:', url, '→', '?endpoint=demand-notes' + urlParams);
    return originalGet.call(this, '?endpoint=demand-notes' + urlParams, config);
  } else if (url === '/options' || url.startsWith('/options/')) {
    // Handle admin options/dropdown data
    console.log('🔧 Options API call:', url, '→', '?endpoint=options');
    return originalGet.call(this, '?endpoint=options', config);
  } else if (url === '/dropdown-options' || url.startsWith('/dropdown-options/')) {
    // Handle dropdown options for admin forms and specific types
    const optionType = url.includes('/') ? url.split('/').pop() : null;
    console.log('🔧 Dropdown Options API call:', url, '→ Type:', optionType);
    
    // Define all dropdown data
    const dropdownData = {
      building: async () => {
        const response = await axios.get('http://localhost/buildings-api.php?endpoint=buildings', config);
        const buildings = response.data.data || [];
        return buildings.map(b => ({ id: b.id, name: b.name, description: b.description }));
      },
      floor: async () => {
        const response = await axios.get('http://localhost/buildings-api.php?endpoint=floors', config);
        const floors = response.data.data || [];
        return floors.map((f, index) => ({ id: index + 1, name: f.floor }));
      },
      department: async () => {
        const response = await axios.get('http://localhost/get-departments.php', config);
        const departments = response.data.data || [];
        return departments.map((d, index) => ({ id: index + 1, name: d.department }));
      },
      room: async () => {
        const response = await axios.get('http://localhost/buildings-api.php?endpoint=rooms', config);
        const rooms = response.data.data || [];
        return rooms.map((r, index) => ({ id: index + 1, name: r.room }));
      },
      condition: async () => {
        const response = await axios.get('http://localhost/get-conditions.php', config);
        const conditions = response.data.data || [];
        return conditions.map((c, index) => ({ id: index + 1, name: c.condition }));
      },
      status: async () => {
        const response = await axios.get('http://localhost/get-statuses.php', config);
        const statuses = response.data.data || [];
        
        // Add common statuses if they don't exist in database
        const commonStatuses = ['active', 'inactive', 'maintenance', 'disposed'];
        const existingStatuses = statuses.map(s => s.status);
        
        const allStatuses = [...statuses];
        commonStatuses.forEach(status => {
          if (!existingStatuses.includes(status)) {
            allStatuses.push({ status: status });
          }
        });
        
        return allStatuses.map((s, index) => ({ id: index + 1, name: s.status }));
      }
    };
    
    if (optionType && dropdownData[optionType]) {
      // Handle specific type request (e.g., /dropdown-options/building)
      return dropdownData[optionType]().then(data => ({
        data: {
          success: true,
          data: data
        }
      }));
    } else {
      // Handle general dropdown options request
      return Promise.all(
        Object.entries(dropdownData).map(async ([key, getter]) => [key, await getter()])
      ).then(results => {
        const allData = Object.fromEntries(results);
        return {
          data: {
            success: true,
            data: allData
          }
        };
      });
    };
  } else if (url === '/users/privileges/available') {
    // Handle available user privileges
    console.log('🔧 User Privileges API call:', url, '→', '?endpoint=users-privileges-available');
    return originalGet.call(this, '?endpoint=users-privileges-available', config);
  } else {
    console.log('🔄 No routing match, using original URL:', url);
  }
  return originalGet.call(this, url, config);
};

API.post = function(url, data, config) {
  console.log(' API.post called with URL:', url);
  console.log('📦 POST Data:', JSON.stringify(data, null, 2));
  if (url === '/it-assets') {
    console.log(' IT Assets POST: → REAL DATABASE');
    return axios.post('http://localhost/real-it-assets-api.php?endpoint=it-assets', data, config);
  } else if (url === '/users') {
    console.log(' Users POST: → Laravel API');
    console.log('👤 User data being sent:', data);
    const token = localStorage.getItem('token');
    const headers = {
      ...(config?.headers || {}),
      'Authorization': token ? `Bearer ${token}` : undefined,
      'Content-Type': 'application/json'
    };
    return originalPost.call(this, '/users', data, { ...config, headers });
  } else if (url === '/change-password') {
    console.log('🔐 Change Password POST: → Laravel API');
    const token = localStorage.getItem('token');
    const headers = {
      ...(config?.headers || {}),
      'Authorization': token ? `Bearer ${token}` : undefined,
      'Content-Type': 'application/json'
    };
    return originalPost.call(this, '/change-password', data, { ...config, headers });
  } else if (url.startsWith('/demand-notes')) {
    console.log(' Demand Notes POST:', url, '→', '?endpoint=demand-notes');
    return originalPost.call(this, '?endpoint=demand-notes', data, config);
  }
  return originalPost.call(this, url, data, config);
};

API.put = function(url, data, config) {
  console.log('🚀 API.put called with URL:', url);
  if (url.startsWith('/it-assets/')) {
    // Handle PUT requests for individual assets like /it-assets/1033
    const assetIdMatch = url.match(/^\/it-assets\/(\d+)$/);
    if (assetIdMatch) {
      const assetId = assetIdMatch[1];
      console.log('🔧 IT Assets PUT: → REAL DATABASE (update asset)', assetId);
      console.log('🔧 PUT URL:', `http://localhost/real-it-assets-api.php?endpoint=it-asset&id=${assetId}`);
      return axios.put(`http://localhost/real-it-assets-api.php?endpoint=it-asset&id=${assetId}`, data, config);
    }
  } else if (url.startsWith('/users/')) {
    console.log('🔧 Users PUT: → Laravel API');
    // Ensure token is included in the request
    const token = localStorage.getItem('token');
    const headers = {
      ...API.defaults.headers.common,
      ...(config?.headers || {}),
      'Authorization': token ? `Bearer ${token}` : undefined,
    };
    return axios.put(`${apiBase}${url}`, data, { ...config, headers });
  }
  console.log('🔄 No PUT routing match, using original URL:', url);
  return axios.put(url, data, config);
};

API.delete = function(url, config) {
  if (url.startsWith('/it-assets/')) {
    // For delete operations, we might need to handle the ID
    const id = url.split('/').pop();
    return originalDelete.call(this, `?endpoint=it-assets&action=delete&id=${id}`, config);
  } else if (url.startsWith('/users/')) {
    console.log('🔧 Users DELETE: → Laravel API');
    return originalDelete.call(this, url, config);
  }
  return originalDelete.call(this, url, config);
};

export default API;
