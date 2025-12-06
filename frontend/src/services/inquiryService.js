import API from './api';

export const inquiryService = {
  // Submit a new inquiry (public endpoint)
  submitInquiry: async (inquiryData) => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/inquiries/submit', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(inquiryData)
      });
      return await response.json();
    } catch (error) {
      throw new Error('Failed to submit inquiry: ' + error.message);
    }
  },

  // Get all inquiries (authenticated)
  getInquiries: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams(params).toString();
      const url = queryParams ? `/inquiries?${queryParams}` : '/inquiries';
      const response = await API.get(url);
      return response.data;
    } catch (error) {
      throw new Error('Failed to fetch inquiries: ' + error.message);
    }
  },

  // Get a specific inquiry by ID (authenticated)
  getInquiry: async (id) => {
    try {
      const response = await API.get(`/inquiries/${id}`);
      return response.data;
    } catch (error) {
      throw new Error('Failed to fetch inquiry: ' + error.message);
    }
  },

  // Update an inquiry (authenticated)
  updateInquiry: async (id, inquiryData) => {
    try {
      const response = await API.put(`/inquiries/${id}`, inquiryData);
      return response.data;
    } catch (error) {
      throw new Error('Failed to update inquiry: ' + error.message);
    }
  },

  // Delete an inquiry (authenticated)
  deleteInquiry: async (id) => {
    try {
      const response = await API.delete(`/inquiries/${id}`);
      return response.data;
    } catch (error) {
      throw new Error('Failed to delete inquiry: ' + error.message);
    }
  },

  // Close an inquiry (authenticated)
  closeInquiry: async (id) => {
    try {
      const response = await API.post(`/inquiries/${id}/close`);
      return response.data;
    } catch (error) {
      throw new Error('Failed to close inquiry: ' + error.message);
    }
  },

  // Open an inquiry (authenticated)
  openInquiry: async (id) => {
    try {
      const response = await API.post(`/inquiries/${id}/open`);
      return response.data;
    } catch (error) {
      throw new Error('Failed to open inquiry: ' + error.message);
    }
  },

  // Search inquiries (authenticated)
  searchInquiries: async (searchTerm, status = null) => {
    try {
      const params = { search: searchTerm };
      if (status) params.status = status;
      return await inquiryService.getInquiries(params);
    } catch (error) {
      throw new Error('Failed to search inquiries: ' + error.message);
    }
  },

  // Get inquiry report with statistics (authenticated)
  getInquiryReport: async (filters = {}) => {
    try {
      const queryParams = new URLSearchParams(filters).toString();
      const url = queryParams ? `/inquiries/report?${queryParams}` : '/inquiries/report';
      const response = await API.get(url);
      return response.data;
    } catch (error) {
      throw new Error('Failed to fetch inquiry report: ' + error.message);
    }
  }
};

export default inquiryService;
