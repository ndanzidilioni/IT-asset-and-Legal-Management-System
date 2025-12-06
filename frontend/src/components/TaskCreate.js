import React, { useState } from 'react';
import API from '../services/api';
import { useEffect } from 'react';

function TaskCreate(){
    const [title,setTitle] = useState('');
    const [description,setDescription] = useState('');
    const [clientId,setClientId] = useState('');
    const [developerId,setDeveloperId] = useState('');
  const [users,setUsers] = useState([]);
  const [user,setUser] = useState(null);

   const handleSubmit = async e => {
  e.preventDefault();

  const token = localStorage.getItem('token');
  if (!token) {
    alert('You must be logged in as an admin to create tasks.');
    return;
  }

  try {
    const res = await API.post('/tasks', {
      title,
      description,
      client_id: clientId,
      developer_id: developerId
    });
    alert('Task Created!');
  } catch (err) {
    if (err.response) {
      // server responded with status code outside 2xx
      alert(`Error ${err.response.status}: ${err.response.data.message || 'Server error'}`);
    } else {
      alert('Network error or no response from server.');
    }
  }
}

useEffect(()=>{
  API.get('/user').then(r=>setUser(r.data)).catch(()=>setUser(null));
  // load users for select (clients and developers)
  API.get('/users').then(r=>setUsers(r.data)).catch(()=>setUsers([]));
},[]);

    return (
      <div className="card">
        <h2>Create Task</h2>
        {user && user.role !== 'admin' && (
          <div className="card">You must be an admin to create tasks.</div>
        )}
        <form onSubmit={handleSubmit}>
          <input placeholder="Title" value={title} onChange={e=>setTitle(e.target.value)} />
          <textarea placeholder="Description" value={description} onChange={e=>setDescription(e.target.value)} />
          <label>Client</label>
          <select value={clientId} onChange={e=>setClientId(e.target.value)}>
            <option value="">-- select client --</option>
            {users.filter(u=>u.role==='client').map(u=> <option key={u.id} value={u.id}>{u.name} ({u.email})</option>)}
          </select>
          <label>Assign to developer (optional)</label>
          <select value={developerId} onChange={e=>setDeveloperId(e.target.value)}>
            <option value="">-- select developer --</option>
            {users.filter(u=>u.role==='developer').map(u=> <option key={u.id} value={u.id}>{u.name} ({u.email})</option>)}
          </select>
          <div className="top-actions">
            <button className="primary" type="submit">Create Task</button>
          </div>
        </form>
      </div>
    )
}

export default TaskCreate;
