import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';

const CourtSchedule = () => {
  const navigate = useNavigate();
  const [hearings, setHearings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadSchedule();
  }, []);

  const loadSchedule = async () => {
    try {
      console.log('Loading court schedules...');
      const allSchedulesRes = await legalApi.courtSchedules.getAll();
      
      console.log('Court schedules response:', allSchedulesRes);
      
      // Handle different response formats
      let scheduleData = [];
      if (allSchedulesRes.success && allSchedulesRes.data) {
        // Handle paginated response
        scheduleData = allSchedulesRes.data.data || allSchedulesRes.data;
      } else if (Array.isArray(allSchedulesRes.data)) {
        scheduleData = allSchedulesRes.data;
      } else if (Array.isArray(allSchedulesRes)) {
        scheduleData = allSchedulesRes;
      }
      
      console.log('Processed schedule data:', scheduleData);
      
      setHearings(Array.isArray(scheduleData) ? scheduleData : []);
      setError('');
      setLoading(false);
    } catch (error) {
      console.error('Error loading schedule:', error);
      setError('Failed to load schedules: ' + (error.message || 'Unknown error'));
      setHearings([]);
      setLoading(false);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div style={{padding: '20px'}}>
      <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px'}}>
        <h1 style={{margin: 0}}>⚖️ Court Schedule</h1>
        <button 
          onClick={() => navigate('/legal/schedule-meeting')}
          style={{
            padding: '10px 20px',
            background: '#3b82f6',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            cursor: 'pointer',
            fontSize: '16px',
            fontWeight: '600'
          }}
        >
          + Schedule Meeting
        </button>
      </div>

      {error && (
        <div style={{
          padding: '16px',
          background: '#fee2e2',
          color: '#991b1b',
          borderRadius: '8px',
          marginBottom: '20px',
          border: '1px solid #fecaca'
        }}>
          ❌ {error}
        </div>
      )}
      
      <div style={{marginTop: '30px'}}>
        <h2>📅 Scheduled Meetings & Hearings ({hearings.length})</h2>
        {hearings.length === 0 ? (
          <p style={{color: '#6b7280', padding: '20px', background: '#f9fafb', borderRadius: '8px'}}>
            No scheduled meetings yet. Click "Schedule Meeting" to create one.
          </p>
        ) : (
          hearings.map(schedule => (
            <div key={schedule.id} style={{padding: '20px', background: '#f0f9ff', borderRadius: '8px', marginBottom: '15px', borderLeft: '4px solid #3b82f6'}}>
              <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'start'}}>
                <div>
                  <h3 style={{margin: 0}}>{schedule.title}</h3>
                  <p style={{color: '#6b7280', margin: '5px 0'}}>{schedule.event_type}</p>
                </div>
                <span style={{padding: '6px 12px', background: '#dbeafe', borderRadius: '12px', fontSize: '14px'}}>
                  {schedule.status || 'Scheduled'}
                </span>
              </div>
              <div style={{display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '15px', marginTop: '15px'}}>
                <div><strong>📅 Date:</strong> {schedule.event_date}</div>
                <div><strong>🕐 Time:</strong> {schedule.event_time || 'N/A'}</div>
                <div><strong>⏱️ Duration:</strong> {schedule.duration_minutes || 60} minutes</div>
                <div><strong>📍 Location:</strong> {schedule.location || 'N/A'}</div>
                {schedule.description && <div style={{gridColumn: 'span 2'}}><strong>📋 Description:</strong> {schedule.description}</div>}
                {schedule.notes && <div style={{gridColumn: 'span 2'}}><strong>📝 Notes:</strong> {schedule.notes}</div>}
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

export default CourtSchedule;
