import React, { useState } from 'react';
import CascadingDropdown from './CascadingDropdown';
import './CascadingDropdownDemo.css';

const CascadingDropdownDemo = () => {
  const [selections, setSelections] = useState({});
  const [isValid, setIsValid] = useState(false);

  const handleSelectionChange = (newSelections) => {
    setSelections(newSelections);
    
    // Check if all selections are made
    const allSelected = newSelections.building && 
                       newSelections.floor && 
                       newSelections.department && 
                       newSelections.room;
    setIsValid(allSelected);
  };

  const handleSubmit = () => {
    if (isValid) {
      alert(`Selected Location:\nBuilding: ${selections.building}\nFloor: ${selections.floor}\nDepartment: ${selections.department}\nRoom: ${selections.room}`);
    } else {
      alert('Please select all location options');
    }
  };

  const handleReset = () => {
    setSelections({});
    setIsValid(false);
  };

  return (
    <div className="cascading-demo">
      <div className="demo-header">
        <h2>Cascading Location Dropdown Demo</h2>
        <p>Select a building to see available floors, then select a floor to see departments, and finally select a department to see rooms.</p>
      </div>

      <div className="demo-content">
        <CascadingDropdown 
          onSelectionChange={handleSelectionChange}
          showLabels={true}
        />

        <div className="demo-actions">
          <button 
            onClick={handleSubmit}
            disabled={!isValid}
            className="submit-btn"
          >
            Submit Selection
          </button>
          <button 
            onClick={handleReset}
            className="reset-btn"
          >
            Reset
          </button>
        </div>

        <div className="demo-info">
          <h3>How it works:</h3>
          <ul>
            <li><strong>Building:</strong> Shows all available buildings from your IT assets</li>
            <li><strong>Floor:</strong> Shows only floors that exist in the selected building</li>
            <li><strong>Department:</strong> Shows only departments that exist on the selected floor in the selected building</li>
            <li><strong>Room:</strong> Shows only rooms that exist in the selected department on the selected floor in the selected building</li>
          </ul>
          
          <h3>Current Selection:</h3>
          <div className="selection-display">
            <div><strong>Building:</strong> {selections.building || 'Not selected'}</div>
            <div><strong>Floor:</strong> {selections.floor || 'Not selected'}</div>
            <div><strong>Department:</strong> {selections.department || 'Not selected'}</div>
            <div><strong>Room:</strong> {selections.room || 'Not selected'}</div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CascadingDropdownDemo;















