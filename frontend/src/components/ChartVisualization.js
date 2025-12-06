import React, { useState } from 'react';
import './ChartVisualization.css';

const ChartVisualization = ({ title, data, defaultType = 'histogram' }) => {
  const [chartType, setChartType] = useState(defaultType);

  if (!data || data.length === 0) {
    return (
      <div className="chart-visualization">
        <div className="chart-header">
          <h3>{title}</h3>
        </div>
        <div className="no-data">No data available</div>
      </div>
    );
  }

  // Calculate total for pie chart percentages
  const total = data.reduce((sum, item) => sum + (item.count || item.value || 0), 0);
  
  // Prepare data with percentages
  const chartData = data.map(item => ({
    ...item,
    percentage: total > 0 ? ((item.count || item.value || 0) / total * 100).toFixed(1) : 0,
    value: item.count || item.value || 0
  }));

  const maxValue = Math.max(...chartData.map(item => item.value));

  const HistogramChart = () => (
    <div className="histogram-chart">
      {chartData.map((item, index) => (
        <div key={index} className="histogram-item">
          <div className="histogram-bar-container">
            <div 
              className="histogram-bar" 
              style={{ 
                height: `${(item.value / maxValue) * 200}px`,
                backgroundColor: getBarColor(index)
              }}
            >
              <div className="histogram-value">{item.value}</div>
            </div>
          </div>
          <div className="histogram-label">
            {item.label || item.category || item.condition || item.department || item.brand || item.status}
          </div>
        </div>
      ))}
    </div>
  );

  const PieChart = () => {
    let cumulativePercentage = 0;
    
    return (
      <div className="pie-chart-container">
        <div className="pie-chart">
          <svg width="200" height="200" viewBox="0 0 200 200">
            {chartData.map((item, index) => {
              const startAngle = (cumulativePercentage / 100) * 360;
              const endAngle = ((cumulativePercentage + parseFloat(item.percentage)) / 100) * 360;
              
              const startAngleRad = (startAngle - 90) * (Math.PI / 180);
              const endAngleRad = (endAngle - 90) * (Math.PI / 180);
              
              const x1 = 100 + 80 * Math.cos(startAngleRad);
              const y1 = 100 + 80 * Math.sin(startAngleRad);
              const x2 = 100 + 80 * Math.cos(endAngleRad);
              const y2 = 100 + 80 * Math.sin(endAngleRad);
              
              const largeArcFlag = parseFloat(item.percentage) > 50 ? 1 : 0;
              
              const pathData = [
                `M 100 100`,
                `L ${x1} ${y1}`,
                `A 80 80 0 ${largeArcFlag} 1 ${x2} ${y2}`,
                `Z`
              ].join(' ');
              
              cumulativePercentage += parseFloat(item.percentage);
              
              return (
                <path
                  key={index}
                  d={pathData}
                  fill={getPieColor(index)}
                  stroke="#fff"
                  strokeWidth="2"
                />
              );
            })}
          </svg>
        </div>
        <div className="pie-legend">
          {chartData.map((item, index) => (
            <div key={index} className="pie-legend-item">
              <div 
                className="pie-legend-color" 
                style={{ backgroundColor: getPieColor(index) }}
              ></div>
              <span className="pie-legend-label">
                {item.label || item.category || item.condition || item.department || item.brand || item.status}
              </span>
              <span className="pie-legend-value">
                {item.value} ({item.percentage}%)
              </span>
            </div>
          ))}
        </div>
      </div>
    );
  };

  const getBarColor = (index) => {
    const colors = [
      '#3b82f6', '#ef4444', '#f59e0b', '#10b981', 
      '#8b5cf6', '#f97316', '#06b6d4', '#84cc16'
    ];
    return colors[index % colors.length];
  };

  const getPieColor = (index) => {
    const colors = [
      '#3b82f6', '#ef4444', '#f59e0b', '#10b981', 
      '#8b5cf6', '#f97316', '#06b6d4', '#84cc16'
    ];
    return colors[index % colors.length];
  };

  return (
    <div className="chart-visualization">
      <div className="chart-header">
        <h3>{title}</h3>
        <div className="chart-controls">
          <button 
            className={`chart-type-btn ${chartType === 'histogram' ? 'active' : ''}`}
            onClick={() => setChartType('histogram')}
          >
            📊 Histogram
          </button>
          <button 
            className={`chart-type-btn ${chartType === 'pie' ? 'active' : ''}`}
            onClick={() => setChartType('pie')}
          >
            🥧 Pie Chart
          </button>
        </div>
      </div>
      
      <div className="chart-content">
        {chartType === 'histogram' ? <HistogramChart /> : <PieChart />}
      </div>
      
      <div className="chart-summary">
        <div className="summary-stats">
          <span className="total-items">Total Items: {chartData.length}</span>
          <span className="total-value">Total Count: {total}</span>
        </div>
      </div>
    </div>
  );
};

export default ChartVisualization;
