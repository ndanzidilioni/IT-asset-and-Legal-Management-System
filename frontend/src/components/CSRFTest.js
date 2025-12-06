import React, { useState } from 'react';
import axios from 'axios';

const CSRFTest = () => {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(false);

  const addLog = (message) => {
    console.log(message);
    setLogs(prev => [...prev, `${new Date().toLocaleTimeString()}: ${message}`]);
  };

  const testCSRF = async () => {
    setLoading(true);
    setLogs([]);
    
    try {
      // Create axios instance with exact same config as your api.js
      const AuthAPI = axios.create({
        baseURL: 'http://127.0.0.1:8000',
        withCredentials: true,
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
      });

      addLog('Step 1: Getting CSRF cookie...');
      
      // Step 1: Get CSRF cookie
      const csrfResponse = await AuthAPI.get('/sanctum/csrf-cookie');
      addLog(`CSRF response status: ${csrfResponse.status}`);
      
      // Check cookies in browser
      const cookies = document.cookie;
      addLog(`Current cookies: ${cookies || 'None'}`);
      
      // Step 2: Try login
      addLog('Step 2: Attempting login...');
      
      const loginResponse = await AuthAPI.post('/login-web', {
        login: 'admin',
        password: 'admin123'
      });
      
      addLog(`Login response: ${JSON.stringify(loginResponse.data)}`);
      
    } catch (error) {
      addLog(`Error: ${error.message}`);
      if (error.response) {
        addLog(`Response status: ${error.response.status}`);
        addLog(`Response data: ${JSON.stringify(error.response.data)}`);
        addLog(`Response headers: ${JSON.stringify(error.response.headers)}`);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: '20px', maxWidth: '800px' }}>
      <h2>CSRF Debug Test</h2>
      <button onClick={testCSRF} disabled={loading}>
        {loading ? 'Testing...' : 'Test CSRF Flow'}
      </button>
      
      <div style={{ marginTop: '20px', backgroundColor: '#f5f5f5', padding: '10px', borderRadius: '4px' }}>
        <h3>Debug Logs:</h3>
        {logs.length === 0 ? (
          <p>Click "Test CSRF Flow" to start debugging</p>
        ) : (
          logs.map((log, index) => (
            <div key={index} style={{ fontFamily: 'monospace', fontSize: '12px', marginBottom: '5px' }}>
              {log}
            </div>
          ))
        )}
      </div>
      
      <div style={{ marginTop: '20px', fontSize: '12px', color: '#666' }}>
        <p><strong>Expected flow:</strong></p>
        <ol>
          <li>GET /sanctum/csrf-cookie should set XSRF-TOKEN and laravel_session cookies</li>
          <li>POST /login-web should include X-XSRF-TOKEN header and cookies</li>
          <li>Login should succeed or return specific error (not CSRF mismatch)</li>
        </ol>
        
        <p><strong>Check DevTools Network tab for:</strong></p>
        <ul>
          <li>CSRF cookie request: Response should set cookies for 127.0.0.1</li>
          <li>Login request: Should include X-XSRF-TOKEN header and Cookie header</li>
        </ul>
      </div>
    </div>
  );
};

export default CSRFTest;
