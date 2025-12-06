import React, { useState, useEffect } from 'react';
import API from '../services/api';
import './AdminPanel.css';
import CascadingDropdown from './CascadingDropdown';

const AdminPanel = () => {
  const [activeTab, setActiveTab] = useState('users');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // State for each category
  const [buildings, setBuildings] = useState([]);
  const [floors, setFloors] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [rooms, setRooms] = useState([]);
  const [conditions, setConditions] = useState([]);
  const [Status, setStatus] = useState([]);

  // User management state
  const [users, setUsers] = useState([]);
  const [showUserForm, setShowUserForm] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [userForm, setUserForm] = useState({
    fname: '',
    mname: '',
    lname: '',
    email: '',
    username: '',
    password: '',
    role: 'user',
    status: 'active',
    privileges: []
  });

  const [availablePrivileges, setAvailablePrivileges] = useState({});

  // Form states
  const [newItem, setNewItem] = useState({ name: '', description: '' });
  const [editingItem, setEditingItem] = useState(null);
  const [locationSelection, setLocationSelection] = useState({
    building: '',
    floor: '',
    department: '',
    room: ''
  });

  // Predefined options for conditions and Status
  const predefinedConditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];
  const predefinedStatus = ['active', 'inactive', 'maintenance', 'disposed'];

  useEffect(() => {
    fetchAllOptions();
    if (activeTab === 'users') {
      fetchUsers();
      fetchAvailablePrivileges();
    }
  }, [activeTab]);

  const fetchAllOptions = async () => {
    try {
      setLoading(true);
      console.log('Fetching all options...');
      const response = await API.get('/dropdown-options');
      console.log('Fetch response:', response.data);
      
      if (response.data.success) {
        const options = response.data.data || {};
        console.log('Options data:', options);
        
        // The API returns an object with type keys, not a flat array
        // Ensure we always have arrays and filter out null/undefined items
        const buildingsData = (options.building || []).filter(item => item && item.id);
        const floorsData = (options.floor || []).filter(item => item && item.id);
        const departmentsData = (options.department || []).filter(item => item && item.id);
        const roomsData = (options.room || []).filter(item => item && item.id);
        const conditionsData = (options.condition || []).filter(item => item && item.id);
        const StatusData = (options.status || []).filter(item => item && item.id);
        
        console.log('Buildings data:', buildingsData);
        
        setBuildings(buildingsData);
        setFloors(floorsData);
        setDepartments(departmentsData);
        setRooms(roomsData);
        setConditions(conditionsData);
        setStatus(StatusData);
      }
    } catch (err) {
      console.error('Fetch error:', err);
      setError('Failed to load options: ' + err.message);
      // Set empty arrays on error to prevent further issues
      setBuildings([]);
      setFloors([]);
      setDepartments([]);
      setRooms([]);
      setConditions([]);
      setStatus([]);
    } finally {
      setLoading(false);
    }
  };

  const handleAdd = async (type) => {
    if (!newItem.name.trim()) {
      setError('Name is required');
      return;
    }

    try {
      setLoading(true);
      setError('');
      setSuccess('');
      
      console.log('Adding item:', { type, value: newItem.name.trim(), label: newItem.description.trim() || newItem.name.trim() });
      
      const response = await API.post('/dropdown-options', {
        type: type,
        value: newItem.name.trim(),
        label: newItem.description.trim() || newItem.name.trim(),
        sort_order: 0
      });

      console.log('Add response:', response.data);

      if (response.data.success) {
        setSuccess(`${type.replace('_', ' ')} added successfully!`);
        setNewItem({ name: '', description: '' });
        // Wait a moment then refresh
        setTimeout(() => {
          fetchAllOptions();
        }, 500);
      } else {
        setError('Failed to add item: ' + (response.data.message || 'Unknown error'));
      }
    } catch (err) {
      console.error('Add error:', err);
      setError('Failed to add item: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = async (item) => {
    // Check if this is an "existing" item (from database values, not dropdown_options table)
    if (item.id && item.id.toString().startsWith('existing_')) {
      setError('Cannot edit existing values from assets. These are automatically generated from your IT assets.');
      return;
    }

    try {
      setLoading(true);
      setError('');
      const response = await API.put(`/dropdown-options/${item.id}`, {
        value: item.name,
        label: item.description,
        is_active: item.is_active
      });

      if (response.data.success) {
        setSuccess('Item updated successfully!');
        setEditingItem(null);
        fetchAllOptions();
      }
    } catch (err) {
      setError('Failed to update item: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    // Check if this is an "existing" item (from database values, not dropdown_options table)
    if (id && id.toString().startsWith('existing_')) {
      setError('Cannot delete existing values from assets. These are automatically generated from your IT assets.');
      return;
    }

    if (!window.confirm('Are you sure you want to delete this item?')) {
      return;
    }

    try {
      setLoading(true);
      setError('');
      await API.delete(`/dropdown-options/${id}`);
      setSuccess('Item deleted successfully!');
      fetchAllOptions();
    } catch (err) {
      setError('Failed to delete item: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const handleToggle = async (item) => {
    // Check if this is an "existing" item (from database values, not dropdown_options table)
    if (item.id && item.id.toString().startsWith('existing_')) {
      setError('Cannot modify existing values from assets. These are automatically generated from your IT assets.');
      return;
    }

    try {
      setLoading(true);
      setError('');
      await API.post(`/dropdown-options/${item.id}/toggle`);
      setSuccess('Item status updated!');
      fetchAllOptions();
    } catch (err) {
      setError('Failed to toggle item: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  // User Management Functions
  const fetchUsers = async () => {
    try {
      setLoading(true);
      console.log('🔍 Fetching users...');
      const response = await API.get('/users');
      console.log('👥 Users API response:', response);
      console.log('👥 Users data:', response.data);
      
      if (response.data.success) {
        // Ensure users have all required fields
        const normalizedUsers = response.data.data.map(user => ({
          ...user,
          mname: user.mname || '', // Ensure mname exists
          privileges: user.privileges || [] // Ensure privileges exists as array
        }));
        setUsers(normalizedUsers);
        console.log('✅ Users loaded successfully:', normalizedUsers.length, 'users');
      } else {
        console.log('❌ Users API response success is false');
        setError('Failed to fetch users: API returned success=false');
      }
    } catch (err) {
      console.error('🚨 Users fetch error:', err);
      setError('Failed to fetch users: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleUserFormChange = (e) => {
    const { name, value } = e.target;
    setUserForm(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleCreateUser = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      setError('');
      
      const response = await API.post('/users', userForm);
      if (response.data.success) {
        setSuccess('User created successfully');
        setUserForm({ fname: '', mname: '', lname: '', email: '', username: '', password: '', role: 'user', status: 'active', privileges: [] });
        setShowUserForm(false);
        fetchUsers();
      } else {
        setError(response.data.message || 'Failed to create user');
      }
    } catch (err) {
      console.error('Create user error:', err.response?.data);
      const errorMessage = err.response?.data?.message || err.message;
      const validationErrors = err.response?.data?.errors;
      
      if (validationErrors) {
        const errorList = Object.values(validationErrors).flat().join(', ');
        setError('Validation failed: ' + errorList);
      } else {
        setError('Failed to create user: ' + errorMessage);
      }
    } finally {
      setLoading(false);
    }
  };

  const handleEditUser = (user) => {
    console.log('🔧 Editing user ID:', user?.id);
    console.log('🔧 Editing user name:', user?.fname, user?.lname);
    setEditingUser(user);
    const formData = {
      fname: user.fname || '',
      mname: user.mname || '',
      lname: user.lname || '',
      email: user.email || '',
      username: user.username || '',
      password: '',
      role: user.role || 'user',
      status: user.status || 'active',
      privileges: user.privileges || []
    };
    console.log('🔧 Setting form data keys:', Object.keys(formData));
    setUserForm(formData);
    setShowUserForm(true);
  };

  const handleUpdateUser = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      setError('');
      
      const updateData = { ...userForm };
      if (!updateData.password) {
        delete updateData.password; // Don't update password if empty
      }
      
      const response = await API.put(`/users/${editingUser.id}`, updateData);
      if (response.data.success) {
        setSuccess('User updated successfully');
        setUserForm({ fname: '', mname: '', lname: '', email: '', username: '', password: '', role: 'user', status: 'active', privileges: [] });
        setEditingUser(null);
        setShowUserForm(false);
        fetchUsers();
      } else {
        setError(response.data.message || 'Failed to update user');
      }
    } catch (err) {
      console.error('Update user error:', err.response?.data);
      const errorMessage = err.response?.data?.message || err.message;
      const validationErrors = err.response?.data?.errors;
      
      if (validationErrors) {
        const errorList = Object.values(validationErrors).flat().join(', ');
        setError('Validation failed: ' + errorList);
      } else {
        setError('Failed to update user: ' + errorMessage);
      }
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteUser = async (userId) => {
    if (!window.confirm('Are you sure you want to delete this user?')) {
      return;
    }
    
    try {
      setLoading(true);
      setError('');
      
      const response = await API.delete(`/users/${userId}`);
      if (response.data.success) {
        setSuccess('User deleted successfully');
        fetchUsers();
      } else {
        setError(response.data.message || 'Failed to delete user');
      }
    } catch (err) {
      setError('Failed to delete user: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const cancelUserForm = () => {
    setUserForm({ fname: '', mname: '', lname: '', email: '', username: '', password: '', role: 'user', status: 'active', privileges: [] });
    setEditingUser(null);
    setShowUserForm(false);
  };

  const fetchAvailablePrivileges = async () => {
    try {
      const response = await API.get('/users/privileges/available');
      if (response.data.success) {
        // Transform array of privilege objects to key-value mapping
        const privilegesArray = response.data.data || [];
        const privilegesMap = {};
        
        privilegesArray.forEach(privilege => {
          if (privilege && privilege.name && privilege.display_name) {
            privilegesMap[privilege.name] = privilege.display_name;
          }
        });
        
        setAvailablePrivileges(privilegesMap);
      }
    } catch (err) {
      console.error('Failed to fetch privileges:', err);
      setAvailablePrivileges({}); // Set empty object on error
    }
  };

  const handlePrivilegeToggle = (privilegeKey) => {
    setUserForm(prev => {
      const currentPrivileges = prev.privileges || [];
      const hasPrivilege = currentPrivileges.includes(privilegeKey);
      
      return {
        ...prev,
        privileges: hasPrivilege 
          ? currentPrivileges.filter(p => p !== privilegeKey)
          : [...currentPrivileges, privilegeKey]
      };
    });
  };

  const renderUserManagement = () => {
    // Debug logs outside JSX
    console.log('🔍 Rendering users count:', users.length);
    console.log('🔍 Available privileges:', availablePrivileges);
    return (
      <div className="user-management">
        <div className="user-header">
          <h3>User Management</h3>
          <div style={{display: 'flex', gap: '1rem'}}>
            <button 
              onClick={fetchUsers}
              className="refresh-btn"
              disabled={loading}
            >
              🔄 Refresh Users
            </button>
            <button 
              onClick={() => setShowUserForm(true)}
              className="add-user-btn"
              disabled={loading}
            >
              + Add New User
            </button>
          </div>
        </div>

        {/* User Form */}
        {showUserForm && (
          <div className="user-form-overlay">
            <div className="user-form">
              <h4>{editingUser ? 'Edit User' : 'Create New User'}</h4>
              <form onSubmit={editingUser ? handleUpdateUser : handleCreateUser}>
                <div className="form-group">
                  <label>First Name *</label>
                  <input
                    type="text"
                    name="fname"
                    value={userForm.fname}
                    onChange={handleUserFormChange}
                    required
                    placeholder="Enter first name"
                  />
                </div>
                
                <div className="form-group">
                  <label>Middle Name</label>
                  <input
                    type="text"
                    name="mname"
                    value={userForm.mname}
                    onChange={handleUserFormChange}
                    placeholder="Enter middle name (optional)"
                  />
                </div>
                
                <div className="form-group">
                  <label>Last Name *</label>
                  <input
                    type="text"
                    name="lname"
                    value={userForm.lname}
                    onChange={handleUserFormChange}
                    required
                    placeholder="Enter last name"
                  />
                </div>
                
                <div className="form-group">
                  <label>Email *</label>
                  <input
                    type="email"
                    name="email"
                    value={userForm.email}
                    onChange={handleUserFormChange}
                    required
                    placeholder="Enter email address"
                  />
                </div>
                
                <div className="form-group">
                  <label>Username *</label>
                  <input
                    type="text"
                    name="username"
                    value={userForm.username}
                    onChange={handleUserFormChange}
                    required
                    placeholder="Enter username"
                  />
                </div>
                
                <div className="form-group">
                  <label>Password {editingUser ? '(leave blank to keep current)' : '*'}</label>
                  <input
                    type="password"
                    name="password"
                    value={userForm.password}
                    onChange={handleUserFormChange}
                    required={!editingUser}
                    placeholder="Enter password"
                  />
                </div>
                
                <div className="form-group">
                  <label>Role *</label>
                  <select
                    name="role"
                    value={userForm.role}
                    onChange={handleUserFormChange}
                    required
                  >
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    <option value="lawyer">Lawyer</option>
                    <option value="ict">ICT</option>
                    <option value="client">Client</option>
                  </select>
                </div>
                
                <div className="form-group">
                  <label>Status *</label>
                  <select
                    name="status"
                    value={userForm.status}
                    onChange={handleUserFormChange}
                    required
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                
                {/* Privileges Section */}
                {Object.keys(availablePrivileges).length > 0 && (
                  <div className="form-group">
                    <label>Permissions & Privileges</label>
                    <div style={{
                      display: 'grid',
                      gridTemplateColumns: 'repeat(2, 1fr)',
                      gap: '12px',
                      marginTop: '8px',
                      padding: '12px',
                      backgroundColor: '#f8fafc',
                      borderRadius: '6px',
                      border: '1px solid #e2e8f0'
                    }}>
                      {Object.entries(availablePrivileges).map(([key, label]) => (
                        <div key={key} style={{
                          display: 'flex',
                          alignItems: 'center',
                          gap: '8px'
                        }}>
                          <input
                            type="checkbox"
                            id={`priv-${key}`}
                            checked={(userForm.privileges || []).includes(key)}
                            onChange={() => handlePrivilegeToggle(key)}
                            style={{ cursor: 'pointer' }}
                          />
                          <label 
                            htmlFor={`priv-${key}`}
                            style={{
                              margin: 0,
                              cursor: 'pointer',
                              fontSize: '14px',
                              fontWeight: 'normal'
                            }}
                          >
                            {typeof label === 'string' ? label : key}
                          </label>
                        </div>
                      ))}
                    </div>
                    <div style={{
                      marginTop: '8px',
                      fontSize: '13px',
                      color: '#64748b'
                    }}>
                      Selected: {(userForm.privileges || []).length} permission(s)
                    </div>
                  </div>
                )}
                
                <div className="form-actions">
                  <button type="submit" disabled={loading} className="save-btn">
                    {editingUser ? 'Update User' : 'Create User'}
                  </button>
                  <button type="button" onClick={cancelUserForm} className="cancel-btn">
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Users List */}
        <div className="users-list">
          <div className="users-table">
            <div className="table-header">
              <div>Name</div>
              <div>Email</div>
              <div>Username</div>
              <div>Role</div>
              <div>Status</div>
              <div>Privileges</div>
              <div>Created</div>
              <div>Actions</div>
            </div>
            {users.length === 0 ? (
              <div className="table-row">
                <div colSpan="8" style={{textAlign: 'center', padding: '2rem', color: '#64748b'}}>
                  {loading ? 'Loading users...' : 'No users found'}
                </div>
              </div>
            ) : users.map((user) => (
              <div key={user.id} className="table-row">
                <div>{String(user.fname || '')} {user.mname ? String(user.mname) + ' ' : ''}{String(user.lname || '')}</div>
                <div>{String(user.email || '')}</div>
                <div>{String(user.username || '')}</div>
                <div>
                  <span className={`role-badge ${user.role || ''}`}>
                    {String(user.role || '').charAt(0).toUpperCase() + String(user.role || '').slice(1)}
                  </span>
                </div>
                <div>
                  <span className={`status-badge ${user.status || ''}`}>
                    {String(user.status || '').charAt(0).toUpperCase() + String(user.status || '').slice(1)}
                  </span>
                </div>
                <div>
                  {user.privileges && user.privileges.length > 0 ? (
                    <div style={{ fontSize: '12px' }}>
                      <span style={{ fontWeight: 'bold' }}>{user.privileges.length}</span>
                      <div style={{ fontSize: '11px', color: '#64748b', marginTop: '4px' }}>
                        {user.privileges.slice(0, 2).map(p => {
                          // Ensure we return a string, not an object
                          const privilegeLabel = availablePrivileges[p];
                          return typeof privilegeLabel === 'string' ? privilegeLabel : p;
                        }).join(', ')}
                        {user.privileges.length > 2 && ` +${user.privileges.length - 2} more`}
                      </div>
                    </div>
                  ) : (
                    <span style={{ color: '#94a3b8', fontSize: '12px' }}>None</span>
                  )}
                </div>
                <div>{new Date(user.created_at).toLocaleDateString()}</div>
                <div className="actions">
                  <button 
                    onClick={() => handleEditUser(user)}
                    className="edit-btn"
                    disabled={loading}
                  >
                    Edit
                  </button>
                  <button 
                    onClick={() => handleDeleteUser(user.id)}
                    className="delete-btn"
                    disabled={loading}
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  };

  const renderCategorySection = (type, items, title, predefinedOptions = null) => {
    const currentItems = items || [];
    
    return (
      <div className="category-section">
        <h3>{title}</h3>
        {/* Location Selector helper for Building/Floor/Department/Room */}
        {(type === 'building' || type === 'floor' || type === 'department' || type === 'room') && (
          <div className="location-helper" style={{marginBottom:12}}>
            <CascadingDropdown
              onSelectionChange={(sel)=>setLocationSelection(sel)}
              showLabels={true}
            />
            <div style={{display:'flex',alignItems:'center',gap:8,marginTop:8}}>
              <button
                type="button"
                className="add-btn"
                onClick={() => {
                  const map = {
                    building: locationSelection.building,
                    floor: locationSelection.floor,
                    department: locationSelection.department,
                    room: locationSelection.room
                  };
                  const value = map[type] || '';
                  setNewItem(prev => ({...prev, name: value}));
                }}
                disabled={
                  (type === 'building' && !locationSelection.building) ||
                  (type === 'floor' && !locationSelection.floor) ||
                  (type === 'department' && !locationSelection.department) ||
                  (type === 'room' && !locationSelection.room) ||
                  loading
                }
              >
                Use selected {title.slice(0,-1).toLowerCase()}
              </button>
              <span style={{fontSize:13,color:'#374151'}}>
                Selected: {locationSelection.building || '—'} → {locationSelection.floor || '—'} → {locationSelection.department || '—'} → {locationSelection.room || '—'}
              </span>
            </div>
          </div>
        )}
        
        {/* Add new item form */}
        <div className="add-form">
          <input
            type="text"
            placeholder={`Add new ${title.toLowerCase()}`}
            value={newItem.name}
            onChange={(e) => setNewItem({ ...newItem, name: e.target.value })}
            onKeyPress={(e) => e.key === 'Enter' && handleAdd(type)}
          />
          <input
            type="text"
            placeholder="Description (optional)"
            value={newItem.description}
            onChange={(e) => setNewItem({ ...newItem, description: e.target.value })}
            onKeyPress={(e) => e.key === 'Enter' && handleAdd(type)}
          />
          <button 
            onClick={() => handleAdd(type)}
            disabled={loading}
            className="add-btn"
          >
            Add
          </button>
        </div>

        {/* Predefined options info */}
        {predefinedOptions && (
          <div className="predefined-info">
            <p><strong>Predefined options:</strong> {predefinedOptions.join(', ')}</p>
          </div>
        )}

        {/* Items list */}
        <div className="items-list">
          {currentItems.filter(item => item && item.id).map((item) => (
            <div key={item.id} className={`item-row ${!item.is_active ? 'inactive' : ''}`}>
              {editingItem?.id === item.id ? (
                <div className="edit-form">
                  <input
                    type="text"
                    value={editingItem.value || editingItem.name}
                    onChange={(e) => setEditingItem({ ...editingItem, name: e.target.value, value: e.target.value })}
                  />
                  <input
                    type="text"
                    value={editingItem.label || editingItem.description || ''}
                    onChange={(e) => setEditingItem({ ...editingItem, description: e.target.value, label: e.target.value })}
                    placeholder="Description"
                  />
                  <button onClick={() => handleEdit(editingItem)} className="save-btn">Save</button>
                  <button onClick={() => setEditingItem(null)} className="cancel-btn">Cancel</button>
                </div>
              ) : (
                <>
                  <div className="item-info">
                    <span className="item-name">{item?.value || item?.name || 'Unnamed'}</span>
                    {(item?.label || item?.description) && <span className="item-description"> - {item?.label || item?.description}</span>}
                    <span className={`status-badge ${item?.is_active ? 'active' : 'inactive'}`}>
                      {item?.is_active ? 'Active' : 'Inactive'}
                    </span>
                    {item?.id && item.id.toString().startsWith('existing_') && (
                      <span className="status-badge readonly">Auto-generated</span>
                    )}
                  </div>
                  <div className="item-actions">
                    <button 
                      onClick={() => setEditingItem(item)} 
                      className="edit-btn"
                      disabled={item?.id && item.id.toString().startsWith('existing_')}
                    >
                      Edit
                    </button>
                    <button 
                      onClick={() => handleToggle(item)} 
                      className="toggle-btn"
                      disabled={item?.id && item.id.toString().startsWith('existing_')}
                    >
                      {item?.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                    <button 
                      onClick={() => handleDelete(item?.id)} 
                      className="delete-btn"
                      disabled={item?.id && item.id.toString().startsWith('existing_')}
                    >
                      Delete
                    </button>
                  </div>
                </>
              )}
            </div>
          ))}
          
          {currentItems.length === 0 && (
            <div className="no-items">No {title.toLowerCase()} found. Add some above.</div>
          )}
        </div>
      </div>
    );
  };

  const tabs = [
    { id: 'users', label: 'Users', type: 'users' },
    { id: 'buildings', label: 'Buildings', type: 'building' },
    { id: 'floors', label: 'Floors', type: 'floor' },
    { id: 'departments', label: 'Departments', type: 'department' },
    { id: 'rooms', label: 'Rooms', type: 'room' },
    { id: 'conditions', label: 'Conditions', type: 'condition' },
    { id: 'Status', label: 'Status', type: 'status' }
  ];

  return (
    <div className="admin-panel">
      <div className="admin-header">
        <h1>System Administration</h1>
        <p>Manage dropdown options for the IT Asset system</p>
      </div>

      {/* Messages */}
      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}

      {/* Tabs */}
      <div className="admin-tabs">
        {tabs.map(tab => (
          <button
            key={tab.id}
            className={`tab-btn ${activeTab === tab.id ? 'active' : ''}`}
            onClick={() => setActiveTab(tab.id)}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Tab Content */}
      <div className="tab-content">
        {activeTab === 'users' && renderUserManagement()}
        {activeTab === 'buildings' && renderCategorySection('building', buildings, 'Buildings')}
        {activeTab === 'floors' && renderCategorySection('floor', floors, 'Floors')}
        {activeTab === 'departments' && renderCategorySection('department', departments, 'Departments')}
        {activeTab === 'rooms' && renderCategorySection('room', rooms, 'Rooms')}
        {activeTab === 'conditions' && renderCategorySection('condition', conditions, 'Conditions', predefinedConditions)}
        {activeTab === 'Status' && renderCategorySection('status', Status, 'Status', predefinedStatus)}
      </div>

      {loading && <div className="loading-overlay">Loading...</div>}
    </div>
  );
};

export default AdminPanel;
