import React, { useState, useEffect, useRef } from 'react';
import API from '../services/api';
import './DropdownWithAdd.css';

const DropdownWithAdd = ({ 
  name, 
  value, 
  onChange, 
  placeholder, 
  type, 
  required = false,
  className = '',
  disabled = false 
}) => {
  const [options, setOptions] = useState([]);
  const [isOpen, setIsOpen] = useState(false);
  const [newOption, setNewOption] = useState('');
  const [isAddingNew, setIsAddingNew] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  useEffect(() => {
    fetchOptions();
    
    // Close dropdown when clicking outside
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
        setIsAddingNew(false);
        setNewOption('');
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [type]);

  const fetchOptions = async () => {
    try {
      setLoading(true);
      console.log('🔍 DropdownWithAdd fetching options for type:', type);
      const response = await API.get(`/dropdown-options/${type}`);
      console.log('📋 DropdownWithAdd response for', type, ':', response);
      console.log('📋 DropdownWithAdd response data for', type, ':', response.data);
      if (type === 'floor') {
        console.log('🏢 Floor options specifically:', response.data.data);
      }
      if (type === 'condition') {
        console.log('⚙️ Condition options specifically:', response.data.data);
      }
      if (type === 'status') {
        console.log('📊 Status options specifically:', response.data.data);
        console.log('📊 Status options count:', response.data.data ? response.data.data.length : 0);
        if (response.data.data) {
          response.data.data.forEach((status, index) => {
            console.log(`📊 Status ${index + 1}: ID=${status.id}, Name="${status.name}"`);
          });
        }
      }
      
      if (response.data.success) {
        const optionsData = Array.isArray(response.data.data) ? response.data.data : [];
        setOptions(optionsData);
        console.log('✅ DropdownWithAdd options loaded:', optionsData.length, 'items');
        setError('');
      } else {
        console.log('❌ DropdownWithAdd API response success is false');
        setError('Failed to load options: API returned success=false');
      }
    } catch (err) {
      console.error('🚨 DropdownWithAdd fetch error:', err);
      setError('Failed to load options');
    } finally {
      setLoading(false);
    }
  };

  const handleSelect = (optionValue) => {
    // Prefer event-style when a field name is provided
    if (typeof onChange === 'function') {
      if (name) {
        onChange({ target: { name, value: optionValue } });
      } else {
        onChange(optionValue);
      }
    }
    setIsOpen(false);
    setIsAddingNew(false);
    setNewOption('');
  };

  const handleAddNew = async () => {
    if (!newOption.trim()) return;

    try {
      setLoading(true);
      setError('');
      
      const response = await API.post('/dropdown-options', {
        type,
        value: newOption.trim(),
        label: newOption.trim()
      });

      if (response.data.success) {
        // Add the new option to the list
        const newOptionData = {
          value: newOption.trim(),
          label: newOption.trim(),
          is_predefined: true
        };
        
        setOptions(prev => [...prev, newOptionData].sort((a, b) => 
          a.label.localeCompare(b.label)
        ));
        
        // Select the new option
        handleSelect(newOption.trim());
      }
    } catch (err) {
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat();
        setError(errors.join(', '));
      } else {
        setError('Failed to add new option');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (isAddingNew) {
        handleAddNew();
      } else {
        setIsAddingNew(true);
        setTimeout(() => inputRef.current?.focus(), 0);
      }
    } else if (e.key === 'Escape') {
      setIsOpen(false);
      setIsAddingNew(false);
      setNewOption('');
    }
  };

  const getDisplayValue = () => {
    if (!value) return '';
    const option = options.find(opt => (opt.value || opt.name) === value);
    return option ? (option.label || option.name) : value;
  };

  const filteredOptions = (options || []).filter(option => {
    if (!option) return false;
    const searchText = option.label || option.name || '';
    const searchValue = newOption || '';
    return searchText.toLowerCase().includes(searchValue.toLowerCase());
  });

  return (
    <div className={`dropdown-with-add ${className}`} ref={dropdownRef}>
      <div 
        className={`dropdown-trigger ${isOpen ? 'open' : ''} ${disabled ? 'disabled' : ''}`}
        onClick={() => !disabled && setIsOpen(!isOpen)}
        onKeyDown={handleKeyDown}
        tabIndex={disabled ? -1 : 0}
        role="button"
        aria-expanded={isOpen}
        aria-haspopup="listbox"
      >
        <span className={`dropdown-value ${!value ? 'placeholder' : ''}`}>
          {getDisplayValue() || placeholder}
        </span>
        <span className="dropdown-arrow">▼</span>
      </div>

      {isOpen && (
        <div className="dropdown-menu">
          {loading && (
            <div className="dropdown-loading">
              <div className="spinner"></div>
              <span>Loading options...</span>
            </div>
          )}

          {error && (
            <div className="dropdown-error">
              {error}
            </div>
          )}

          {!loading && (
            <>
              {/* Add new option input */}
              <div className="add-new-option">
                <input
                  ref={inputRef}
                  type="text"
                  value={newOption}
                  onChange={(e) => setNewOption(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      e.preventDefault();
                      handleAddNew();
                    } else if (e.key === 'Escape') {
                      setIsAddingNew(false);
                      setNewOption('');
                    }
                  }}
                  placeholder={`Add new ${type}...`}
                  className="new-option-input"
                />
                <button
                  type="button"
                  onClick={handleAddNew}
                  disabled={!newOption.trim() || loading}
                  className="add-option-btn"
                >
                  Add
                </button>
              </div>

              {/* Options list */}
              <div className="options-list">
                {filteredOptions.length === 0 ? (
                  <div className="no-options">
                    No {type} options found
                  </div>
                ) : (
                  filteredOptions.map((option) => {
                    const optionValue = option.value || option.name;
                    const optionLabel = option.label || option.name;
                    return (
                      <div
                        key={option.id || optionValue}
                        className={`option-item ${value === optionValue ? 'selected' : ''} ${!option.is_predefined ? 'existing' : ''}`}
                        onClick={() => handleSelect(optionValue)}
                        role="option"
                        aria-selected={value === optionValue}
                      >
                        <span className="option-label">{optionLabel}</span>
                        {!option.is_predefined && (
                          <span className="option-badge">Existing</span>
                        )}
                      </div>
                    );
                  })
                )}
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
};

export default DropdownWithAdd;
