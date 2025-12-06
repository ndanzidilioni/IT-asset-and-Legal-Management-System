import React, { useEffect, useState } from 'react';
import API from '../services/api';

function ClientPanel(){
  const [title,setTitle] = useState('');
  const [description,setDescription] = useState('');
  const [tasks,setTasks] = useState([]);

  useEffect(()=>{
    API.get('/tasks').then(r=>setTasks(r.data)).catch(()=>setTasks([]));
  },[]);

  const submit = async ()=>{
    try{
      const res = await API.post('/tasks/request',{title,description});
      setTasks([res.data,...tasks]);
      setTitle(''); setDescription('');
    }catch(e){window.alert('Submit failed');}
  }

  return (
    <div>
      <h1>Client Panel</h1>
      <div className="card">
        <h3>Report an issue / Request work</h3>
        <input placeholder="Short title" value={title} onChange={e=>setTitle(e.target.value)} />
        <textarea placeholder="Describe the requirement or issue" rows={6} value={description} onChange={e=>setDescription(e.target.value)} />
        <div className="top-actions">
          <button className="primary" onClick={submit}>Submit Request</button>
        </div>
      </div>

      <div className="card" style={{marginTop:12}}>
        <h3>Your Requests</h3>
        <ul className="task-list">
          {tasks.map(t=> (
            <li key={t.id}>
              <div style={{flex:1}}>
                <div style={{fontWeight:600}}>{t.title}</div>
                <div style={{fontSize:13,color:'#6b7280'}}>{t.description}</div>
                <div style={{fontSize:12,color:'#6b7280'}}>Status: {t.status}</div>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  )
}

export default ClientPanel;
