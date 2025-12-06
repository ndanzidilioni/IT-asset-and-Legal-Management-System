import React, { useEffect, useState } from 'react';
import legalApi from '../../services/legalApi';

const TestContracts = () => {
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    const testAPI = async () => {
      try {
        console.log('Testing contract API...');
        const response = await legalApi.contracts.getAll(2024);
        console.log('API Response:', response);
        setData(response);
      } catch (err) {
        console.error('API Error:', err);
        setError(err.message);
      }
    };
    testAPI();
  }, []);

  return (
    <div style={{ padding: '20px' }}>
      <h1>Contract API Test</h1>
      {error && <div style={{ color: 'red' }}>Error: {error}</div>}
      {data && (
        <div>
          <h2>Success!</h2>
          <pre>{JSON.stringify(data, null, 2)}</pre>
        </div>
      )}
    </div>
  );
};

export default TestContracts;
