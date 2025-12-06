import React, { useState, useEffect, useRef } from 'react';
import API from '../services/api';
import ITAssetForm from './ITAssetForm';
import './ITAssetReport.css';

const ITAssetReport = () => {
  const [assets, setAssets] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const pageSize = 10; // show at least ten items per page
  const [totalAssets, setTotalAssets] = useState(0);
  const [pagination, setPagination] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingAsset, setEditingAsset] = useState(null);
  const [statistics, setStatistics] = useState({});
  const [filters, setFilters] = useState({
    status: '',
    condition: '',
    department: '',
    building: '',
    search: ''
  });
  const tableContainerRef = useRef(null);

  // Import/Export state
  const [showImportModal, setShowImportModal] = useState(false);
  const [importFile, setImportFile] = useState(null);
  // CSV is the only supported format for import
  const [importResults, setImportResults] = useState(null);

  useEffect(() => {
    fetchAssets();
    fetchStatistics();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentPage]); // Re-fetch when page changes

  const fetchAssets = async () => {
    try {
      setLoading(true);
      setError('');
      
      const params = new URLSearchParams();
      Object.keys(filters).forEach(key => {
        if (filters[key]) {
          params.append(key, filters[key]);
        }
      });
      
      // Add pagination parameters
      params.append('page', currentPage);
      params.append('per_page', pageSize);

      const response = await API.get(`/it-assets?${params.toString()}`);
      
      console.log('🔍 IT Assets response:', response);
      console.log('🔍 Response data:', response.data);
      console.log('🔍 Response success:', response.data.success);
      console.log('🔍 Response data.data:', response.data.data);
      
      // Handle both direct array response and wrapped response
      if (response.data.success || Array.isArray(response.data)) {
        // Ensure we always set an array, even if data is null/undefined
        const assetsData = Array.isArray(response.data) ? response.data : 
                          Array.isArray(response.data.data) ? response.data.data : [];
        setAssets(assetsData);
        
        // Handle pagination info from server response
        if (response.data.pagination) {
          setPagination(response.data.pagination);
          setTotalAssets(response.data.pagination.total || 0);
        } else {
          // Fallback for direct array response
          setTotalAssets(assetsData.length);
          setPagination({
            current_page: 1,
            per_page: assetsData.length,
            total: assetsData.length,
            last_page: 1
          });
        }
        
        console.log('✅ Assets loaded successfully:', assetsData.length, 'assets');
      } else {
        console.log('❌ Response success is false or no data');
        setAssets([]); // Ensure assets is always an array
        setError('Failed to fetch assets');
      }
    } catch (err) {
      console.error('🚨 IT Assets fetch error:', err);
      console.error('🚨 Error response:', err.response);
      console.error('🚨 Error config:', err.config);
      setAssets([]); // Ensure assets is always an array even on error
      setError('Error loading assets: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  const fetchStatistics = async () => {
    try {
      console.log('Fetching statistics from:', API.defaults.baseURL + '/it-assets/statistics');
      const response = await API.get('/it-assets/statistics');
      console.log('📊 Statistics response:', response);
      console.log('📊 Statistics data:', response.data);
      
      if (response.data.success && response.data.data) {
        setStatistics(response.data.data);
        console.log('✅ Statistics loaded successfully:', response.data.data);
      } else if (response.data && !response.data.error) {
        // Handle direct response format
        setStatistics(response.data);
        console.log('✅ Statistics loaded (direct format):', response.data);
      } else {
        console.error('❌ Statistics fetch failed:', response.data);
      }
    } catch (err) {
      console.error('Failed to fetch statistics:', err);
      console.error('Request URL was:', err.config?.url);
      console.error('Base URL was:', err.config?.baseURL);
    }
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleFilterSubmit = (e) => {
    e.preventDefault();
    setCurrentPage(1); // Reset to first page when applying filters
    fetchAssets();
  };

  const clearFilters = () => {
    setFilters({
      status: '',
      condition: '',
      department: '',
      building: '',
      search: ''
    });
    setCurrentPage(1); // Reset to first page when clearing filters
    fetchAssets();
  };

  const handleEdit = (asset) => {
    setEditingAsset(asset);
    setShowForm(true);
  };

  const handleDelete = async (assetId) => {
    if (!window.confirm('Are you sure you want to delete this asset?')) {
      return;
    }

    try {
      await API.delete(`/it-assets/${assetId}`);
      fetchAssets();
      fetchStatistics();
    } catch (err) {
      setError('Failed to delete asset: ' + err.message);
    }
  };

  const handleFormSuccess = () => {
    setShowForm(false);
    setEditingAsset(null);
    fetchAssets();
    fetchStatistics();
  };

  const handleFormCancel = () => {
    setShowForm(false);
    setEditingAsset(null);
  };

  // Import/Export Functions
  const handleExportAssets = async (format) => {
    try {
      setLoading(true);
      setError('');
      
      const response = await API.get(`/it-assets/export/${format}`, {
        responseType: 'blob'
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `it_assets_${new Date().toISOString().split('T')[0]}.${format === 'excel' ? 'xlsx' : 'csv'}`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      
      alert(`IT Assets exported successfully as ${format.toUpperCase()}`);
    } catch (err) {
      setError('Failed to export assets: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const handleDownloadTemplate = async (format) => {
    try {
      setLoading(true);
      setError('');
      
      const response = await API.get(`/it-assets/template?format=${format}`, {
        responseType: 'blob'
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `it_asset_import_template.${format === 'excel' ? 'xlsx' : 'csv'}`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      
      alert('Template downloaded successfully');
    } catch (err) {
      setError('Failed to download template: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const handleImportAssets = async () => {
    if (!importFile) {
      setError('Please select a file to import');
      return;
    }

    try {
      setLoading(true);
      setError('');
      
      console.log('Import file:', importFile);
      console.log('File name:', importFile?.name);
      console.log('File type:', importFile?.type);
      console.log('File size:', importFile?.size);
      
      const formData = new FormData();
      formData.append('file', importFile);
      
      // Debug: Check what's in FormData
      for (let pair of formData.entries()) {
        console.log('FormData entry:', pair[0], pair[1]);
      }
      
      // Force CSV endpoint; backend supports CSV import
      // Axios will automatically detect FormData and set the correct Content-Type with boundary
      // We just need to ensure we don't override it
      const response = await API.post('/it-assets/import/csv', formData, {
        transformRequest: [(data, headers) => {
          // Let axios handle FormData automatically
          delete headers['Content-Type'];
          return data;
        }]
      });
      
      if (response.data.success) {
        setImportResults(response.data.data);
        alert(`Import completed: ${response.data.data.imported} assets imported`);
        fetchAssets(); // Refresh asset list
        fetchStatistics(); // Refresh statistics
        setShowImportModal(false);
        setImportFile(null);
      }
    } catch (err) {
      setError('Failed to import assets: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const exportCsv = async () => {  // eslint-disable-line no-unused-vars
    try {
      const params = new URLSearchParams();
      Object.keys(filters).forEach(key => {
        if (filters[key]) {
          params.append(key, filters[key]);
        }
      });

      const response = await API.get(`/it-assets/export/csv?${params.toString()}`, {
        responseType: 'blob'
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `it_assets_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      setError('Failed to export CSV: ' + err.message);
    }
  };

  const handleFileUpload = async (event) => {  // eslint-disable-line no-unused-vars
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    // Backend expects the field name 'file'
    formData.append('file', file);

    try {
      const response = await API.post('/it-assets/import/csv', formData, {
        transformRequest: [(data, headers) => {
          delete headers['Content-Type'];
          return data;
        }]
      });

      if (response.data.success) {
        alert(`Import completed! ${response.data.data.imported} assets imported.`);
        fetchAssets();
        fetchStatistics();
      } else {
        setError('Import failed: ' + (response.data.message || 'Unknown error'));
      }
    } catch (err) {
      setError('Import failed: ' + err.message);
    }

    // Reset file input
    event.target.value = '';
  };

  if (loading) {
    return (
      <div className="it-asset-report-container">
        <div className="loading-spinner">
          <div className="spinner"></div>
          <p>Loading IT Asset Report...</p>
        </div>
      </div>
    );
  }

  if (showForm) {
    return (
      <ITAssetForm
        assetId={editingAsset?.id}
        onSuccess={handleFormSuccess}
        onCancel={handleFormCancel}
      />
    );
  }

  return (
    <div className="it-asset-report-container">
      <div className="report-header">
        <h1 className="report-title">IT Asset Register</h1>
        <div className="header-actions">
          <button className="add-button" onClick={() => setShowForm(true)}>
            Add New Asset
          </button>
          <button 
            className="import-button" 
            onClick={() => setShowImportModal(true)}
            disabled={loading}
          >
            📥 Import Assets
          </button>
          <button 
            className="export-button" 
            onClick={() => handleExportAssets('csv')}
            disabled={loading}
          >
            📄 Export CSV
          </button>
          <button 
            className="export-button" 
            onClick={() => handleExportAssets('excel')}
            disabled={loading}
          >
            📊 Export Excel
          </button>
        </div>
      </div>

      {/* Statistics Cards */}
      <div className="statistics-cards">
        <div className="stat-card total">
          <div className="stat-number">{statistics.total || 0}</div>
          <div className="stat-label">Total Assets</div>
        </div>
        <div className="stat-card active">
          <div className="stat-number">{statistics.active || 0}</div>
          <div className="stat-label">Active</div>
        </div>
        <div className="stat-card maintenance">
          <div className="stat-number">{statistics.maintenance || 0}</div>
          <div className="stat-label">Maintenance</div>
        </div>
        <div className="stat-card disposed">
          <div className="stat-number">{statistics.disposed || 0}</div>
          <div className="stat-label">Disposed</div>
        </div>
      </div>

      {/* Filters */}
      <div className="filters-section">
        <form onSubmit={handleFilterSubmit} className="filters-form">
          <div className="filter-group">
            <label htmlFor="search">Search:</label>
            <input
              type="text"
              id="search"
              name="search"
              value={filters.search}
              onChange={handleFilterChange}
              placeholder="Search by asset number, description, or department..."
            />
          </div>

          <div className="filter-group">
            <label htmlFor="status">Status:</label>
            <select
              id="status"
              name="status"
              value={filters.status}
              onChange={handleFilterChange}
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="maintenance">Maintenance</option>
              <option value="disposed">Disposed</option>
            </select>
          </div>

          <div className="filter-group">
            <label htmlFor="condition">Condition:</label>
            <select
              id="condition"
              name="condition"
              value={filters.condition}
              onChange={handleFilterChange}
            >
              <option value="">All Conditions</option>
              <option value="excellent">Excellent</option>
              <option value="good">Good</option>
              <option value="fair">Fair</option>
              <option value="poor">Poor</option>
              <option value="damaged">Damaged</option>
            </select>
          </div>

          <div className="filter-group">
            <label htmlFor="department">Department:</label>
            <input
              type="text"
              id="department"
              name="department"
              value={filters.department}
              onChange={handleFilterChange}
              placeholder="Filter by department..."
            />
          </div>

          <div className="filter-group">
            <label htmlFor="building">Building:</label>
            <input
              type="text"
              id="building"
              name="building"
              value={filters.building}
              onChange={handleFilterChange}
              placeholder="Filter by building..."
            />
          </div>

          <div className="filter-actions">
            <button type="submit" className="filter-btn">Apply Filters</button>
            <button type="button" onClick={clearFilters} className="clear-btn">Clear</button>
            <button type="button" onClick={fetchAssets} className="refresh-btn">Refresh</button>
          </div>
        </form>
      </div>

      {/* Error Message */}
      {error && (
        <div className="error-message">
          <strong>Error:</strong> {error}
        </div>
      )}

      <div className="table-container-wrapper">
        <div className="hscroll-controls">
          <button
            type="button"
            className="hscroll-btn"
            onClick={() => {
              const el = tableContainerRef.current;
              if (el) el.scrollBy({ left: -300, behavior: 'smooth' });
            }}
          >
            ◀
          </button>
          <button
            type="button"
            className="hscroll-btn"
            onClick={() => {
              const el = tableContainerRef.current;
              if (el) el.scrollBy({ left: 300, behavior: 'smooth' });
            }}
          >
            ▶
          </button>
        </div>
        <div className="table-container" ref={tableContainerRef}>
          <table className="asset-table">
            <thead>
            <tr>
              <th>Asset Number</th>
              <th>Asset Description</th>
              <th>Building</th>
              <th>Floor</th>
              <th>Department</th>
              <th>Room</th>
              <th>Condition</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {!Array.isArray(assets) || assets.length === 0 ? (
              <tr>
                <td colSpan="9" className="no-data">
                  {loading ? 'Loading assets...' : 'No assets found matching your criteria.'}
                </td>
              </tr>
            ) : (
              // Server-side pagination - display all assets from current page
              assets.map((asset) => (
                <tr key={asset.id}>
                  <td className="asset-number">{asset.asset_number}</td>
                  <td className="asset-description">{asset.asset_description}</td>
                  <td className="building">{asset.building}</td>
                  <td className="floor">{asset.floor}</td>
                  <td className="department">{asset.department}</td>
                  <td className="room">{asset.room}</td>
                  <td className="condition">
                    <span className={`condition-badge ${asset.condition}`}>
                      {asset.condition}
                    </span>
                  </td>
                  <td className="status">
                    <span className={`status-badge ${asset.status}`}>
                      {asset.status}
                    </span>
                  </td>
                  <td className="actions">
                    <button
                      onClick={() => handleEdit(asset)}
                      className="edit-btn"
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => handleDelete(asset.id)}
                      className="delete-btn"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
      </div>

      {/* Pagination Controls */}
      <div className="pagination">
        {(() => {
          const totalPages = Math.max(1, pagination.last_page || Math.ceil(totalAssets / pageSize));
          const canPrev = currentPage > 1;
          const canNext = currentPage < totalPages;
          
          console.log('📄 Pagination Debug:', {
            currentPage,
            totalPages,
            totalAssets,
            paginationInfo: pagination,
            assetsLength: assets.length,
            pageSize,
            canPrev,
            canNext
          });
          
          return (
            <>
              <button
                className="page-btn"
                onClick={() => {
                  console.log('🔙 Prev button clicked, canPrev:', canPrev);
                  if (canPrev) {
                    setCurrentPage(currentPage - 1);
                  }
                }}
                disabled={!canPrev}
              >
                Prev
              </button>
              <span className="page-info">
                Page {currentPage} of {totalPages}
              </span>
              <button
                className="page-btn"
                onClick={() => {
                  console.log('🔜 Next button clicked, canNext:', canNext);
                  if (canNext) {
                    setCurrentPage(currentPage + 1);
                  }
                }}
                disabled={!canNext}
              >
                Next
              </button>
            </>
          );
        })()}
      </div>

      {/* Summary */}
      <div className="report-summary">
        <p>
          Showing {(assets.length === 0) ? 0 : ((currentPage - 1) * pageSize + 1)}
          -{Math.min(currentPage * pageSize, (currentPage - 1) * pageSize + assets.length)} of {totalAssets} asset(s)
          {statistics.total ? ` (Total in system: ${statistics.total})` : ''}
        </p>
      </div>

      {/* Import Modal */}
      {showImportModal && (
        <div className="import-modal-overlay">
          <div className="import-modal">
            <div className="modal-header">
              <h4>Import IT Assets</h4>
              <button 
                onClick={() => setShowImportModal(false)}
                className="close-btn"
              >
                ×
              </button>
            </div>
            
            <div className="modal-content">
              <div className="import-info">
                <p><strong>Import IT assets from CSV or Excel files</strong></p>
                <p>Download a template file to see the required format.</p>
              </div>
              
              <div className="import-options">
                <div className="template-download">
                  <p>Download template:</p>
                  <button 
                    onClick={() => handleDownloadTemplate('csv')}
                    className="template-btn"
                    disabled={loading}
                  >
                    📄 CSV Template
                  </button>
                </div>
              </div>
              
              <div className="file-upload">
                <label>Select File:</label>
                <input
                  type="file"
                  accept={'.csv,.txt'}
                  onChange={(e) => setImportFile(e.target.files[0])}
                />
                {importFile && (
                  <div className="selected-file">
                    Selected: {importFile.name}
                  </div>
                )}
              </div>
              
              {importResults && (
                <div className="import-results">
                  <h5>Import Results:</h5>
                  <div className="results-summary">
                    <div className="result-item success">
                      ✅ Imported: {importResults.imported}
                    </div>
                    <div className="result-item warning">
                      ⚠️ Skipped: {importResults.skipped}
                    </div>
                    <div className="result-item error">
                      ❌ Errors: {importResults.errors}
                    </div>
                  </div>
                  {importResults.error_details && Array.isArray(importResults.error_details) && importResults.error_details.length > 0 && (
                    <div className="error-details">
                      <h6>Error Details:</h6>
                      <div className="error-list">
                        {importResults.error_details.slice(0, 10).map((error, index) => (
                          <div key={index} className="error-item">
                            {Array.isArray(error.errors) ? error.errors.map((err, errIdx) => (
                              <div key={errIdx}>{err}</div>
                            )) : <div>{error}</div>}
                          </div>
                        ))}
                        {importResults.error_details.length > 10 && (
                          <div className="more-errors">
                            ... and {importResults.error_details.length - 10} more errors
                          </div>
                        )}
                      </div>
                    </div>
                  )}
                </div>
              )}
            </div>
            
            <div className="modal-actions">
              <button 
                onClick={handleImportAssets}
                className="save-btn"
                disabled={loading || !importFile}
              >
                Import Assets
              </button>
              <button 
                onClick={() => {
                  setShowImportModal(false);
                  setImportFile(null);
                  setImportResults(null);
                }}
                className="cancel-btn"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ITAssetReport;


