import React, { useEffect, useState } from 'react';
import API from '../services/api';

function DeveloperPanel(){
  const [availability,setAvailability] = useState('');
  const [msg,setMsg] = useState(null);
  const [tasks,setTasks] = useState([]);

  useEffect(()=>{
    API.get('/availability').then(r=>{
      setAvailability(r.data?.availability || '');
    }).catch(()=>{});

    API.get('/tasks').then(r=>{
      // tasks endpoint returns tasks assigned to developer when role=developer
      setTasks(r.data || []);
    }).catch(()=>{});
  },[]);

  const save = async ()=>{
    try{
      // ensure it's a valid JSON string
      JSON.parse(availability);
    }catch(e){ setMsg('Availability must be valid JSON'); return; }

    try{
      const res = await API.post('/availability',{availability});
      setMsg('Saved');
    }catch(e){ setMsg('Save failed'); }
  }

  const handleReject = async (id)=>{
    try{
      await API.post(`/tasks/${id}/reject`);
      setTasks(tasks.filter(t=>t.id !== id));
    }catch(e){ setMsg('Reject failed'); }
  }

  return (
    <div>
      <h1>Developer Panel</h1>
      <div className="card">
        <h3>Your availability (JSON)</h3>
        <textarea rows={8} value={availability} onChange={e=>setAvailability(e.target.value)} />
        <div className="top-actions">
          <button className="primary" onClick={save}>Save Availability</button>
          {msg && <div style={{color:'#6b7280'}}>{msg}</div>}
        </div>
      </div>

      <div className="card" style={{marginTop:12}}>
        <h3>Assigned Tasks</h3>
        <ul className="task-list">
          {tasks.map(t=> (
            <li key={t.id}>
              <div style={{flex:1}}>
                <div style={{fontWeight:600}}>{t.title}</div>
                <div style={{fontSize:13,color:'#6b7280'}}>{t.description}</div>
              </div>
              <div style={{display:'flex',gap:8}}>
                <div className="task-status">{t.status}</div>
                <button className="secondary" onClick={()=>handleReject(t.id)}>Reject</button>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  )
}

export default DeveloperPanel;
