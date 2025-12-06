import React, { useState, useEffect } from 'react';
import API from '../services/api';
import './CascadingDropdown.css';

const CascadingDropdown = ({ 
  onSelectionChange, 
  initialValues = {},
  disabled = false,
  showLabels = true 
}) => {
  const [selections, setSelections] = useState({
    building: initialValues.building || '',
    floor: initialValues.floor || '',
    department: initialValues.department || '',
    room: initialValues.room || ''
  });

  const [options, setOptions] = useState({
    buildings: [],
    floors: [],
    departments: [],
    rooms: []
  });

  const [loading, setLoading] = useState({
    buildings: false,
    floors: false,
    departments: false,
    rooms: false
  });

  const [error, setError] = useState('');

  // Load buildings on component mount
  useEffect(() => {
    loadBuildings();
  }, []);

  // Load floors when building changes
  useEffect(() => {
    if (selections.building) {
      loadFloors(selections.building);
      // Reset dependent selections
      setSelections(prev => ({
        ...prev,
        floor: '',
        department: '',
        room: ''
      }));
    } else {
      setOptions(prev => ({
        ...prev,
        floors: [],
        departments: [],
        rooms: []
      }));
    }
  }, [selections.building]);

  // Load departments when building and floor change
  useEffect(() => {
    if (selections.building && selections.floor) {
      loadDepartments(selections.building, selections.floor);
      // Reset dependent selections
      setSelections(prev => ({
        ...prev,
        department: '',
        room: ''
      }));
    } else {
      setOptions(prev => ({
        ...prev,
        departments: [],
        rooms: []
      }));
    }
  }, [selections.building, selections.floor]);

  // Load rooms when building, floor, and department change
  useEffect(() => {
    if (selections.building && selections.floor && selections.department) {
      loadRooms(selections.building, selections.floor, selections.department);
      // Reset dependent selections
      setSelections(prev => ({
        ...prev,
        room: ''
      }));
    } else {
      setOptions(prev => ({
        ...prev,
        rooms: []
      }));
    }
  }, [selections.building, selections.floor, selections.department]);

  // Notify parent component when selections change
  useEffect(() => {
    if (onSelectionChange) {
      onSelectionChange(selections);
    }
  }, [selections, onSelectionChange]);

  const loadBuildings = async () => {
    try {
      setLoading(prev => ({ ...prev, buildings: true }));
      setError('');
      
      const response = await API.get('/location-hierarchy/buildings');
      if (response.data.success) {
        setOptions(prev => ({
          ...prev,
          buildings: response.data.data
        }));
      } else {
        setError('Failed to load buildings');
      }
    } catch (err) {
      console.error('Error loading buildings:', err);
      setError('Failed to load buildings: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(prev => ({ ...prev, buildings: false }));
    }
  };

  const loadFloors = async (building) => {
    try {
      setLoading(prev => ({ ...prev, floors: true }));
      setError('');
      
      const response = await API.get('/location-hierarchy/floors', {
        params: { building }
      });
      if (response.data.success) {
        setOptions(prev => ({
          ...prev,
          floors: response.data.data
        }));
      } else {
        setError('Failed to load floors');
      }
    } catch (err) {
      console.error('Error loading floors:', err);
      setError('Failed to load floors: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(prev => ({ ...prev, floors: false }));
    }
  };

  const loadDepartments = async (building, floor) => {
    try {
      setLoading(prev => ({ ...prev, departments: true }));
      setError('');
      
      const response = await API.get('/location-hierarchy/departments', {
        params: { building, floor }
      });
      if (response.data.success) {
        setOptions(prev => ({
          ...prev,
          departments: response.data.data
        }));
      } else {
        setError('Failed to load departments');
      }
    } catch (err) {
      console.error('Error loading departments:', err);
      setError('Failed to load departments: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(prev => ({ ...prev, departments: false }));
    }
  };

  const loadRooms = async (building, floor, department) => {
    try {
      setLoading(prev => ({ ...prev, rooms: true }));
      setError('');
      
      const response = await API.get('/location-hierarchy/rooms', {
        params: { building, floor, department }
      });
      if (response.data.success) {
        setOptions(prev => ({
          ...prev,
          rooms: response.data.data
        }));
      } else {
        setError('Failed to load rooms');
      }
    } catch (err) {
      console.error('Error loading rooms:', err);
      setError('Failed to load rooms: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(prev => ({ ...prev, rooms: false }));
    }
  };

  const handleSelectionChange = (type, value) => {
    setSelections(prev => ({
      ...prev,
      [type]: value
    }));
  };

  const renderDropdown = (type, label, options, loading) => {
    const isDisabled = disabled || loading || 
      (type === 'floor' && !selections.building) ||
      (type === 'department' && (!selections.building || !selections.floor)) ||
      (type === 'room' && (!selections.building || !selections.floor || !selections.department));

    return (
      <div className="cascading-dropdown-item">
        {showLabels && <label htmlFor={type}>{label}</label>}
        <select
          id={type}
          value={selections[type]}
          onChange={(e) => handleSelectionChange(type, e.target.value)}
          disabled={isDisabled}
          className="cascading-dropdown-select"
        >
          <option value="">Select {label}</option>
          {options.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        {loading && <div className="loading-spinner">Loading...</div>}
      </div>
    );
  };

  return (
    <div className="cascading-dropdown">
      {error && <div className="error-message">{error}</div>}
      
      <div className="cascading-dropdown-grid">
        {renderDropdown('building', 'Building', options.buildings, loading.buildings)}
        {renderDropdown('floor', 'Floor', options.floors, loading.floors)}
        {renderDropdown('department', 'Department', options.departments, loading.departments)}
        {renderDropdown('room', 'Room', options.rooms, loading.rooms)}
      </div>
      
      {selections.building && selections.floor && selections.department && selections.room && (
        <div className="selection-summary">
          <strong>Selected Location:</strong> {selections.building} → {selections.floor} → {selections.department} → {selections.room}
        </div>
      )}
    </div>
  );
};

export default CascadingDropdown;
