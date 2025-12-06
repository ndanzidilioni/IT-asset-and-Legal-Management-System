import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import API from '../services/api';

function NavBar(){
  const [tokenPresent, setTokenPresent] = useState(false);
  const [userInfo, setUserInfo] = useState(null);
  const navigate = useNavigate();

  useEffect(()=>{
    const update = ()=>{
      const t = !!localStorage.getItem('token');
      const storedUserInfo = localStorage.getItem('userInfo');
      setTokenPresent(t);
      if(t && storedUserInfo){
        try {
          setUserInfo(JSON.parse(storedUserInfo));
        } catch (e) {
          setUserInfo(null);
        }
      } else {
        setUserInfo(null);
      }
    };
    update();
    const onStorage = () => update();
    window.addEventListener('storage', onStorage);
    return ()=> window.removeEventListener('storage', onStorage);
  },[]);

  const handleLogout = async () => {
    try{
      await API.post('/logout');
    }catch(e){
      // ignore network errors — we'll clear local state anyway
    }
    localStorage.removeItem('token');
    localStorage.removeItem('userInfo');
    setTokenPresent(false);
    setUserInfo(null);
    navigate('/login');
  }

  return (
    <nav>
      <div style={{display:'flex',alignItems:'center',gap:12}}>
        {userInfo && userInfo.role === 'admin' && (
          <>
            <Link to="/dashboard">Dashboard</Link>
            <Link to="/it-assets">IT Assets</Link>
            <Link to="/legal">⚖️ Legal Management</Link>
            <Link to="/audit-logs">🔍 Audit Logs</Link>
          </>
        )}
        {userInfo && userInfo.role === 'ict' && (
          <Link to="/it-assets">IT Assets</Link>
        )}
        {userInfo && userInfo.role === 'lawyer' && (
          <Link to="/legal">⚖️ Legal Management</Link>
        )}
        {userInfo && userInfo.role === 'client' && (
          <Link to="/client">Client Dashboard</Link>
        )}
      </div>
      <div style={{display:'flex',alignItems:'center',gap:12}}>
        {userInfo && (
          <div style={{fontSize:13,color:'#374151'}}>Hello, {userInfo.name || userInfo.username || 'User'} ({userInfo.role})</div>
        )}
        {!tokenPresent && (<>
          <Link to="/login">Login</Link>
          <Link to="/register">Register</Link>
        </>)}
        {userInfo && userInfo.role === 'admin' && (
          <Link to="/admin">Admin Panel</Link>
        )}
        {userInfo && userInfo.role === 'developer' && (
          <Link to="/developer">Developer Panel</Link>
        )}
        {userInfo && userInfo.role === 'client' && (
          <Link to="/client">Client Panel</Link>
        )}
        {tokenPresent && (
          <button className="secondary" onClick={handleLogout}>Logout</button>
        )}
      </div>
    </nav>
  )
}

export default NavBar;
