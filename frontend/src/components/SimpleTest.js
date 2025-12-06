import React, { useState } from 'react';

const SimpleTest = () => {
  const [result, setResult] = useState('');
  const [loading, setLoading] = useState(false);

  const testConnection = async () => {
    setLoading(true);
    setResult('Testing...');
    
    try {
      // Test 1: Direct fetch to backend
      console.log('Testing direct fetch...');
      const response = await fetch('http://localhost:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ login: 'admin', password: 'admin123' })
      });
      
      if (response.ok) {
        const data = await response.json();
        setResult(`✅ Direct fetch successful: ${JSON.stringify(data)}`);
      } else {
        setResult(`❌ Direct fetch failed: ${response.status} ${response.statusText}`);
      }
    } catch (error) {
      setResult(`❌ Direct fetch error: ${error.message}`);
    }
    
    setLoading(false);
  };

  return (
    <div style={{ padding: '20px', maxWidth: '600px', margin: '0 auto' }}>
      <h2>🔧 Simple Connection Test</h2>
      <p>This will test the connection to the backend API directly.</p>
      
      <button 
        onClick={testConnection} 
        disabled={loading}
        style={{
          padding: '10px 20px',
          backgroundColor: loading ? '#ccc' : '#007bff',
          color: 'white',
          border: 'none',
          borderRadius: '5px',
          cursor: loading ? 'not-allowed' : 'pointer',
          marginBottom: '20px'
        }}
      >
        {loading ? 'Testing...' : 'Test Backend Connection'}
      </button>

      {result && (
        <div style={{
          padding: '15px',
          backgroundColor: '#f8f9fa',
          border: '1px solid #dee2e6',
          borderRadius: '5px',
          fontFamily: 'monospace',
          whiteSpace: 'pre-wrap'
        }}>
          {result}
        </div>
      )}

      <div style={{ marginTop: '20px', padding: '15px', backgroundColor: '#e7f3ff', borderRadius: '5px' }}>
        <h3>🔐 Test Credentials</h3>
        <p><strong>Username:</strong> admin</p>
        <p><strong>Email:</strong> admin@system.com</p>
        <p><strong>Password:</strong> admin123</p>
      </div>
    </div>
  );
};

export default SimpleTest;















