// Authentication Service for Legal Management System
const AUTH_API_URL = 'http://localhost:8000/api';

const authService = {
  // Login
  login: async (email, password) => {
    try {
      const response = await fetch(`${AUTH_API_URL}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ login: email, password }),
      });

      const data = await response.json();

      if (data.access_token) {
        // Store token and user data (Laravel returns access_token, not token)
        localStorage.setItem('token', data.access_token);

        // Ensure user object exists before storing
        if (data.user) {
          // Store in both 'user' and 'userInfo' for backward compatibility
          localStorage.setItem('user', JSON.stringify(data.user));
          localStorage.setItem('userInfo', JSON.stringify(data.user));
        } else {
          console.warn('Login successful but no user data received');
        }

        return data;
      }

      throw new Error(data.message || 'Login failed');
    } catch (error) {
      throw error;
    }
  },

  // Register
  register: async (name, email, password, role = 'client') => {
    try {
      const response = await fetch(`${AUTH_API_URL}/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name, email, password, role }),
      });

      const data = await response.json();

      if (data.success && data.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        return data;
      }

      throw new Error(data.message || 'Registration failed');
    } catch (error) {
      throw error;
    }
  },

  // Verify token
  verifyToken: async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) return null;

      const response = await fetch(`${AUTH_API_URL}/verify`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
      });

      const data = await response.json();
      return data.valid ? data.user : null;
    } catch (error) {
      return null;
    }
  },

  // Logout
  logout: () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('userInfo');
  },

  // Get current user
  getCurrentUser: () => {
    // Try 'user' first, then 'userInfo' for backward compatibility
    let userStr = localStorage.getItem('user');
    if (!userStr) {
      userStr = localStorage.getItem('userInfo');
    }
    return userStr ? JSON.parse(userStr) : null;
  },

  // Get token
  getToken: () => {
    return localStorage.getItem('token');
  },

  // Check if user has role
  hasRole: (role) => {
    const user = authService.getCurrentUser();
    if (!user) return false;

    const roleHierarchy = {
      'admin': ['admin', 'lawyer', 'legal_assistant', 'client', 'developer'],
      'lawyer': ['lawyer', 'legal_assistant'],
      'developer': ['developer', 'lawyer', 'legal_assistant'],
      'legal_assistant': ['legal_assistant'],
      'client': ['client']
    };

    const userRole = user.role;
    return roleHierarchy[userRole]?.includes(role) || userRole === role;
  },

  // Check if authenticated
  isAuthenticated: () => {
    return !!localStorage.getItem('token');
  }
};

export default authService;
