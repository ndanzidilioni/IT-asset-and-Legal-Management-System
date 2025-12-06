// Legal Management API Service - CONSOLIDATED (Single Port) - v2.0 UPDATED
const LEGAL_BASE_URL = 'http://localhost:8000';
const LARAVEL_API_BASE_URL = 'http://localhost:8000/api';
const DEMAND_NOTES_API_URL = 'http://localhost:8000/api/demand-notes';
const DEMAND_NOTE_DELETION_API_URL = 'http://localhost:8000/api/demand-note-deletion-requests';
console.log('📦 legalApi.js loaded - v2.0 with cases-api.php, demand-notes-api.php and deletion requests support');

// Helper to get auth headers
const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  return headers;
};

const legalApi = {
  // Case Management
  cases: {
    getAll: async () => {
      console.log('🌐 legalApi.cases.getAll - Fetching from:', `${LARAVEL_API_BASE_URL}/cases`);
      console.log('🔑 Auth headers:', getAuthHeaders());
      
      const response = await fetch(`${LARAVEL_API_BASE_URL}/cases`, {
        headers: getAuthHeaders()
      });
      
      console.log('📡 Response status:', response.status, response.statusText);
      
      if (!response.ok) {
        if (response.status === 401) {
          console.error('❌ Authentication failed - 401');
          throw new Error('Authentication required. Please log in.');
        }
        console.error('❌ Request failed:', response.status, response.statusText);
        throw new Error(`Failed to load cases: ${response.statusText}`);
      }
      
      const data = await response.json();
      console.log('📋 Raw data from API:', data);
      console.log('📋 Data type:', typeof data);
      console.log('📋 Is data an array?', Array.isArray(data));
      console.log('📋 data.data exists?', !!data.data);
      console.log('📋 Is data.data an array?', Array.isArray(data.data));
      
      // Laravel returns { current_page: 1, data: [...], ... } for pagination
      // or { success: true, data: [...] }
      const result = { success: true, data: data.data || data };
      console.log('✅ Returning:', result);
      
      return result;
    },
    getById: async (id) => {
      console.log('🔍 legalApi.cases.getById called with ID:', id);
      const url = `${LARAVEL_API_BASE_URL}/cases/${id}`;
      console.log('🔧 Cases API URL:', url);
      
      const response = await fetch(url, {
        headers: getAuthHeaders()
      });
      
      console.log('📋 Cases API response status:', response.status);
      
      if (!response.ok) {
        if (response.status === 404) {
          console.log('❌ Case not found (404)');
          return { success: false, message: 'Case not found' };
        }
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        console.error('❌ Failed to load case, status:', response.status);
        throw new Error('Failed to load case');
      }
      
      const data = await response.json();
      console.log('✅ Case data loaded:', data);
      return { success: true, data: data.data || data };
    },
    create: async (caseData) => {
      console.log('🔧 Creating case with URL:', `${LARAVEL_API_BASE_URL}/cases`);
      console.log('🔧 Auth headers:', getAuthHeaders());
      console.log('🔧 Case data:', caseData);
      
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
      
      try {
        const response = await fetch(`${LARAVEL_API_BASE_URL}/cases`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(caseData),
          signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        console.log('✅ Response received, status:', response.status);
        
        // Check if response is HTML (authentication error or server error)
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          const htmlText = await response.text();
          console.error('❌ Non-JSON response received:', htmlText);
          
          // Check if it's an authentication error
          if (response.status === 401 || htmlText.includes('Unauthenticated')) {
            const error = new Error('Authentication required. Please log in.');
            error.response = { data: { message: 'Authentication required' } };
            throw error;
          }
          
          const error = new Error('Server returned an error. Please check if you are logged in.');
          error.response = { data: { message: 'Server error', details: htmlText.substring(0, 200) } };
          throw error;
        }
        
        const data = await response.json();
        console.log('✅ Case created successfully:', data);
        
        if (!response.ok) {
          const error = new Error(data.message || 'Failed to create case');
          error.response = { data };
          throw error;
        }
        
        return data;
      } catch (error) {
        clearTimeout(timeoutId);
        
        if (error.name === 'AbortError') {
          console.error('❌ Request timeout after 30 seconds');
          const timeoutError = new Error('Request timed out. The server is not responding.');
          timeoutError.response = { data: { message: 'Request timeout' } };
          throw timeoutError;
        }
        
        console.error('❌ Error creating case:', error);
        throw error;
      }
    },
    update: async (id, caseData) => {
      console.log('🔄 legalApi.cases.update called with ID:', id);
      console.log('🔄 legalApi.cases.update data:', caseData);
      const url = `${LEGAL_BASE_URL}/cases-api.php?endpoint=case&id=${id}`;
      console.log('🔧 Cases Update API URL:', url);
      
      const response = await fetch(url, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(caseData)
      });
      
      console.log('📋 Cases Update API response status:', response.status);
      
      if (!response.ok) {
        console.error('❌ Cases Update API failed, status:', response.status);
        const errorText = await response.text();
        console.error('❌ Cases Update API error response:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Cases Update API response data:', data);
      return data; // Return the full response which includes success flag
    },
    getStatistics: async () => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/cases/statistics`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        throw new Error(`Failed to load statistics: ${response.statusText}`);
      }
      
      const data = await response.json();
      return { success: true, data: data.data || data };
    },
    getDashboard: async () => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/cases/dashboard`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    getDashboard: async () => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/cases/dashboard`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    uploadDocument: async (caseId, file, documentName = null, documentType = 'Other') => {
      console.log('📎 legalApi.cases.uploadDocument called:', { caseId, fileName: file.name, documentType });
      
      const formData = new FormData();
      formData.append('file', file);
      formData.append('document_name', documentName || file.name);
      formData.append('document_type', documentType);
      formData.append('case_id', caseId);
      
      const response = await fetch(`${LEGAL_BASE_URL}/documents-api.php`, {
        method: 'POST',
        body: formData // Don't set Content-Type header for FormData
      });
      
      console.log('📎 Document upload response status:', response.status);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Document upload failed:', errorText);
        throw new Error(`Upload failed: ${response.status} ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Document upload response:', data);
      return data;
    },
    getDocuments: async (caseId) => {
      console.log('📎 legalApi.cases.getDocuments called for case:', caseId);
      
      const response = await fetch(`${LEGAL_BASE_URL}/documents-api.php/documents/${caseId}`);
      
      if (!response.ok) {
        throw new Error(`Failed to fetch documents: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('📎 Documents fetched:', data);
      return data;
    },
    deleteDocument: async (documentId) => {
      console.log('📎 legalApi.cases.deleteDocument called for ID:', documentId);
      
      const response = await fetch(`${LEGAL_BASE_URL}/documents-api.php/documents/${documentId}`, {
        method: 'DELETE'
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Delete failed: ${response.status} ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Document deleted:', data);
      return data;
    },
    downloadDocument: (id) => {
      console.log('📎 legalApi.cases.downloadDocument called for ID:', id);
      window.open(`${LEGAL_BASE_URL}/documents-api.php/documents/${id}/download`, '_blank');
    }
  },

  // Client Management
  clients: {
    getAll: async () => {
      console.log('👥 legalApi.clients.getAll called');
      const response = await fetch(`${LEGAL_BASE_URL}/clients-api.php`, {
        headers: getAuthHeaders()
      });
      
      console.log('📋 Clients API response status:', response.status);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Clients API failed:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Clients API response:', data);
      return data;
    },
    getById: async (id) => {
      console.log('👥 legalApi.clients.getById called with ID:', id);
      const response = await fetch(`${LEGAL_BASE_URL}/clients-api.php?path=/clients/${id}`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Client data loaded:', data);
      return data;
    },
    create: async (clientData) => {
      console.log('👥 legalApi.clients.create called:', clientData);
      const response = await fetch(`${LEGAL_BASE_URL}/clients-api.php`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(clientData)
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Client created:', data);
      return data;
    },
    update: async (id, clientData) => {
      console.log('👥 legalApi.clients.update called:', { id, clientData });
      const response = await fetch(`${LEGAL_BASE_URL}/clients-api.php?path=/clients/${id}`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(clientData)
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Client updated:', data);
      return data;
    },
    delete: async (id) => {
      console.log('👥 legalApi.clients.delete called with ID:', id);
      const response = await fetch(`${LEGAL_BASE_URL}/clients-api.php?path=/clients/${id}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Client deleted:', data);
      return data;
    },
    getStatistics: async () => {
      console.log('👥 legalApi.clients.getStatistics called');
      const response = await fetch(`${LEGAL_BASE_URL}/clients-api.php?path=/clients/statistics`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Client statistics loaded:', data);
      return data;
    }
  },

  // Document Management
  documents: {
    getAll: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/documents`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    getStatistics: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/documents/statistics`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    }
  },

  // Contract Register
  contracts: {
    getAll: async (year, status) => {
      console.log('📄 legalApi.contracts.getAll called:', { year, status });
      
      let url = `${LARAVEL_API_BASE_URL}/contracts`;
      const params = new URLSearchParams();
      if (year) params.append('year', year);
      if (status) params.append('status', status);
      if (params.toString()) url += `?${params.toString()}`;
      
      const response = await fetch(url, {
        headers: getAuthHeaders()
      });
      
      console.log('📋 Contracts API response status:', response.status);
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        const errorText = await response.text();
        console.error('❌ Contracts API failed:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contracts API response:', data);
      // Laravel returns { success: true, data: [...] } or just data array
      return { success: true, data: data.data || data };
    },
    getById: async (id) => {
      console.log('📄 legalApi.contracts.getById called with ID:', id);
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contracts/${id}`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract data loaded:', data);
      return { success: true, data: data.data || data };
    },
    create: async (contractData) => {
      console.log('📄 legalApi.contracts.create called:', contractData);
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contracts`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(contractData)
      });
      
      console.log('📋 Contract create response status:', response.status);
      
      if (!response.ok) {
        if (response.status === 401) {
          const errorData = await response.json().catch(() => ({ message: 'Authentication required' }));
          throw new Error(`HTTP 401: ${JSON.stringify(errorData)}`);
        }
        const errorText = await response.text();
        console.error('❌ Contract create failed:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract created:', data);
      return data;
    },
    update: async (id, contractData) => {
      console.log('📄 legalApi.contracts.update called:', { id, contractData });
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contracts/${id}`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(contractData)
      });
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract updated:', data);
      return data;
    },
    delete: async (id) => {
      console.log('📄 legalApi.contracts.delete called with ID:', id);
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contracts/${id}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract deleted:', data);
      return data;
    },
    getStatistics: async (year) => {
      console.log('📄 legalApi.contracts.getStatistics called with year:', year);
      
      let url = `${LARAVEL_API_BASE_URL}/contracts/statistics`;
      if (year) url += `?year=${year}`;
      
      const response = await fetch(url, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract statistics loaded:', data);
      return { success: true, data: data.data || data };
    },
    getYears: async () => {
      console.log('📄 legalApi.contracts.getYears called');
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contracts/years`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract years loaded:', data);
      return { success: true, data: data.data || data };
    },
    uploadDocument: async (contractId, file, documentName = null, documentType = 'Contract') => {
      const formData = new FormData();
      formData.append('file', file); // Backend expects 'file', not 'document'
      formData.append('document_name', documentName || file.name); // Required
      formData.append('document_type', documentType); // Required
      formData.append('contract_id', contractId);
      
      const token = localStorage.getItem('token');
      const response = await fetch(`${LEGAL_BASE_URL}/documents`, {
        method: 'POST',
        headers: {
          'Authorization': token ? `Bearer ${token}` : ''
        },
        body: formData
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        const error = new Error(data.message || 'Failed to upload document');
        error.response = { data };
        throw error;
      }
      
      return data;
    },
    getDocuments: async (contractId) => {
      console.log('📎 legalApi.contracts.getDocuments called for contract:', contractId);
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contracts/${contractId}/documents`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in.');
        }
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract documents loaded:', data);
      return { success: true, data: data.data || data };
    },
    uploadDocument: async (contractId, file, documentName = null, documentType = 'Contract') => {
      console.log('📎 legalApi.contracts.uploadDocument called:', { contractId, file, documentName, documentType });
      
      const formData = new FormData();
      formData.append('file', file);
      formData.append('document_name', documentName || file.name);
      formData.append('document_type', documentType);
      
      const response = await fetch(`${LEGAL_BASE_URL}/contracts-api.php/contracts/${contractId}/documents`, {
        method: 'POST',
        body: formData
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract document uploaded:', data);
      return data;
    },
    downloadDocument: (id) => {
      console.log('📎 legalApi.contracts.downloadDocument called for ID:', id);
      window.open(`${LEGAL_BASE_URL}/contracts-api.php/documents/${id}/download`, '_blank');
    },
    deleteDocument: async (id) => {
      console.log('📎 legalApi.contracts.deleteDocument called for ID:', id);
      const response = await fetch(`${LEGAL_BASE_URL}/contracts-api.php/documents/${id}`, {
        method: 'DELETE'
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Contract document deleted:', data);
      return data;
    }
  },

  // Contract Deletion Requests (Approval System)
  contractDeletionRequests: {
    getAll: async () => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contract-deletion-requests`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    },
    getPending: async () => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contract-deletion-requests/pending`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    },
    create: async (contractId, reason) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contract-deletion-requests`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ contract_id: contractId, reason })
      });
      const data = await response.json();
      return data;
    },
    approve: async (id, comment) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contract-deletion-requests/${id}/approve`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ comment })
      });
      const data = await response.json();
      return data;
    },
    reject: async (id, comment) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contract-deletion-requests/${id}/reject`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ comment })
      });
      const data = await response.json();
      return data;
    },
    cancel: async (id) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/contract-deletion-requests/${id}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    }
  },

  // Case Deletion Requests (Approval System)
  caseDeletionRequests: {
    getAll: async () => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/case-deletion-requests`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    },
    getPending: async () => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/case-deletion-requests/pending`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    },
    create: async (caseId, reason) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/case-deletion-requests`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ case_id: caseId, reason })
      });
      const data = await response.json();
      return data;
    },
    approve: async (id, comment) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/case-deletion-requests/${id}/approve`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ comment })
      });
      const data = await response.json();
      return data;
    },
    reject: async (id, comment) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/case-deletion-requests/${id}/reject`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ comment })
      });
      const data = await response.json();
      return data;
    },
    cancel: async (id) => {
      const response = await fetch(`${LARAVEL_API_BASE_URL}/case-deletion-requests/${id}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    }
  },

  // Demand Notes Management
  demandNotes: {
    getAll: async () => {
      console.log('📄 legalApi.demandNotes.getAll called');
      const response = await fetch(`${DEMAND_NOTES_API_URL}`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Demand Notes API failed:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Demand Notes API response:', data);
      return data;
    },
    getById: async (id) => {
      console.log('📄 legalApi.demandNotes.getById called with ID:', id);
      const response = await fetch(`${DEMAND_NOTES_API_URL}/${id}`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Demand Note data loaded:', data);
      return data;
    },
    create: async (demandNoteData) => {
      console.log('📄 legalApi.demandNotes.create called:', demandNoteData);
      const response = await fetch(`${DEMAND_NOTES_API_URL}`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(demandNoteData)
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Demand Note create failed:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Demand Note created:', data);
      return data;
    },
    update: async (id, demandNoteData) => {
      console.log('📄 legalApi.demandNotes.update called:', { id, demandNoteData });
      const response = await fetch(`${DEMAND_NOTES_API_URL}/${id}`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(demandNoteData)
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Demand Note updated:', data);
      return data;
    },
    delete: async (id) => {
      console.log('📄 legalApi.demandNotes.delete called with ID:', id);
      const response = await fetch(`${DEMAND_NOTES_API_URL}/${id}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Demand Note deleted:', data);
      return data;
    },
    getStatistics: async () => {
      console.log('📄 legalApi.demandNotes.getStatistics called');
      const response = await fetch(`${DEMAND_NOTES_API_URL}/statistics`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Demand Notes statistics loaded:', data);
      return data;
    },
    addPayment: async (id, paymentData) => {
      console.log('📄 legalApi.demandNotes.addPayment called:', { id, paymentData });
      const response = await fetch(`${DEMAND_NOTES_API_URL}/${id}/payments`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(paymentData)
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Payment added to demand note:', data);
      return data;
    },
    exportCsv: async () => {
      console.log('📄 legalApi.demandNotes.exportCsv called');
      const response = await fetch(`${DEMAND_NOTES_API_URL}/export/csv`, {
        headers: getAuthHeaders()
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const blob = await response.blob();
      console.log('✅ Demand Notes CSV exported');
      return blob;
    }
  },

  // Demand Note Deletion Requests (Approval System)
  demandNoteDeletionRequests: {
    getAll: async () => {
      const response = await fetch(`${DEMAND_NOTE_DELETION_API_URL}`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    },
    getPending: async () => {
      const response = await fetch(`${DEMAND_NOTE_DELETION_API_URL}?status=pending`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    },
    create: async (demandNoteId, reason) => {
      const response = await fetch(`${DEMAND_NOTE_DELETION_API_URL}`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ demand_note_id: demandNoteId, reason })
      });
      const data = await response.json();
      return data;
    },
    approve: async (id, comment) => {
      const response = await fetch(`${DEMAND_NOTE_DELETION_API_URL}/${id}/approve`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ comment })
      });
      const data = await response.json();
      return data;
    },
    reject: async (id, comment) => {
      const response = await fetch(`${DEMAND_NOTE_DELETION_API_URL}/${id}/reject`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ comment })
      });
      const data = await response.json();
      return data;
    },
    cancel: async (id) => {
      const response = await fetch(`${DEMAND_NOTE_DELETION_API_URL}/${id}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return data;
    }
  },

  // Court Scheduling
  courtSchedule: {
    getHearings: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules/hearings`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    getDeadlines: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules/deadlines`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    getToday: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules/today`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    }
  },

  // Billing & Finance
  billing: {
    getInvoices: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/invoices`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    getSummary: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/invoices/summary`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    getTimeEntries: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/time-entries`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    }
  },

  // Compliance & Security (uses existing audit-logs from main app)
  compliance: {
    getAuditLogs: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/audit-logs`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    },
    getStatus: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/audit-logs/statistics`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    }
  },

  // Court Schedules
  courtSchedules: {
    getAll: async () => {
      console.log('📅 legalApi.courtSchedules.getAll called');
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules-api.php`);
      
      console.log('📋 Court Schedules API response status:', response.status);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Court Schedules API failed:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Court Schedules API response:', data);
      return data;
    },
    getById: async (id) => {
      console.log('📅 legalApi.courtSchedules.getById called with ID:', id);
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules-api.php/court-schedules/${id}`);
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Court Schedule data loaded:', data);
      return data;
    },
    create: async (scheduleData) => {
      console.log('📅 legalApi.courtSchedules.create called:', scheduleData);
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules-api.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(scheduleData)
      });
      
      console.log('📋 Court Schedule create response status:', response.status);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Court Schedule create failed:', errorText);
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Court Schedule created:', data);
      return data;
    },
    update: async (id, scheduleData) => {
      console.log('📅 legalApi.courtSchedules.update called:', { id, scheduleData });
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules-api.php/court-schedules/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(scheduleData)
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Court Schedule updated:', data);
      return data;
    },
    delete: async (id) => {
      console.log('📅 legalApi.courtSchedules.delete called with ID:', id);
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules-api.php/court-schedules/${id}`, {
        method: 'DELETE'
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }
      
      const data = await response.json();
      console.log('✅ Court Schedule deleted:', data);
      return data;
    },
    getToday: async () => {
      const response = await fetch(`${LEGAL_BASE_URL}/court-schedules/today`, {
        headers: getAuthHeaders()
      });
      const data = await response.json();
      return { success: true, data };
    }
  },

  // Legal Analytics (derived from other endpoints)
  analytics: {
    getDashboard: async () => {
      // Aggregate data from multiple endpoints
      const [cases, clients, contracts, invoices] = await Promise.all([
        fetch(`${LEGAL_BASE_URL}?endpoint=cases-statistics`, { headers: getAuthHeaders() }).then(r => r.json()),
        fetch(`${LEGAL_BASE_URL}?endpoint=clients-statistics`, { headers: getAuthHeaders() }).then(r => r.json()),
        fetch(`${LEGAL_BASE_URL}?endpoint=contracts-statistics`, { headers: getAuthHeaders() }).then(r => r.json()),
        fetch(`${LEGAL_BASE_URL}?endpoint=invoices-summary`, { headers: getAuthHeaders() }).then(r => r.json())
      ]);
      
      return { cases, clients, contracts, invoices };
    },
    getReports: async () => {
      return { message: 'Reports endpoint - aggregate from multiple sources' };
    }
  }
};

export default legalApi;
