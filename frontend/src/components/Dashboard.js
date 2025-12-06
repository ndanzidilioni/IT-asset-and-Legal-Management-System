import React, { useEffect, useState } from 'react';
import API from '../services/api';
import { useNavigate } from 'react-router-dom';
import { useCallback } from 'react';
import ChartVisualization from './ChartVisualization';

function Dashboard(){
    const [user,setUser] = useState(null);
    const [devOverview, setDevOverview] = useState(null);
    const [assetStats, setAssetStats] = useState(null);
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    useEffect(()=>{
        API.get('/user').then(r=>setUser(r.data)).catch(()=>setUser(null));
        fetchAssetStats();
    },[]);

    // Check authentication status
    useEffect(() => {
        const token = localStorage.getItem('token');
        const userInfo = localStorage.getItem('userInfo');
        console.log('Dashboard - Token exists:', !!token);
        console.log('Dashboard - User info exists:', !!userInfo);
        if (userInfo) {
            console.log('Dashboard - User info:', JSON.parse(userInfo));
        }
    }, []);

    const fetchAssetStats = async () => {
        try {
            setLoading(true);
            console.log('Fetching asset statistics...');
            console.log('Token in localStorage:', localStorage.getItem('token'));
            
            const response = await API.get('/it-assets/statistics');
            console.log('Statistics response:', response.data);
            
            if (response.data.success) {
                setAssetStats(response.data.data);
            }
        } catch (err) {
            console.error('Failed to fetch asset statistics:', err);
            console.error('Error response:', err.response?.data);
            console.error('Error status:', err.response?.status);
            
            // If 401, try to refresh the token
            if (err.response?.status === 401) {
                console.log('🔄 401 error - token might be invalid, redirecting to login');
                localStorage.removeItem('token');
                window.location.href = '/login';
            }
        } finally {
            setLoading(false);
        }
    };


    return (
        <div>
            <h1>Dashboard</h1>
            
            {/* Asset Statistics Section */}
            {loading ? (
                <div className="card" style={{marginBottom: '20px'}}>
                    <h2>IT Asset Summary</h2>
                    <div style={{textAlign: 'center', padding: '20px', color: '#64748b'}}>
                        Loading asset statistics...
                    </div>
                </div>
            ) : assetStats ? (
                <div style={{marginBottom: '20px'}}>
                    <div className="card" style={{marginBottom: '20px'}}>
                        <h2>IT Asset Summary</h2>
                        <div style={{display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px', marginBottom: '20px'}}>
                            <div style={{padding: '16px', backgroundColor: '#f8fafc', borderRadius: '8px', border: '1px solid #e2e8f0'}}>
                                <div style={{fontSize: '24px', fontWeight: 'bold', color: '#1e40af'}}>{assetStats.total || 0}</div>
                                <div style={{color: '#64748b', fontSize: '14px'}}>Total Assets</div>
                            </div>
                            <div style={{padding: '16px', backgroundColor: '#f0fdf4', borderRadius: '8px', border: '1px solid #bbf7d0'}}>
                                <div style={{fontSize: '24px', fontWeight: 'bold', color: '#16a34a'}}>{assetStats.active || 0}</div>
                                <div style={{color: '#64748b', fontSize: '14px'}}>Active</div>
                            </div>
                            <div style={{padding: '16px', backgroundColor: '#fef3c7', borderRadius: '8px', border: '1px solid #fde68a'}}>
                                <div style={{fontSize: '24px', fontWeight: 'bold', color: '#d97706'}}>{assetStats.maintenance || 0}</div>
                                <div style={{color: '#64748b', fontSize: '14px'}}>Maintenance</div>
                            </div>
                            <div style={{padding: '16px', backgroundColor: '#fee2e2', borderRadius: '8px', border: '1px solid #fecaca'}}>
                                <div style={{fontSize: '24px', fontWeight: 'bold', color: '#dc2626'}}>{assetStats.disposed || 0}</div>
                                <div style={{color: '#64748b', fontSize: '14px'}}>Disposed</div>
                            </div>
                        </div>
                        <div style={{display: 'flex', gap: '12px', flexWrap: 'wrap'}}>
                            <button className="primary" onClick={()=>navigate('/it-assets')}>View All Assets</button>
                            <button className="secondary" onClick={fetchAssetStats}>Refresh Stats</button>
                        </div>
                    </div>
                    
                    {/* Asset Status Chart Visualization */}
                    <ChartVisualization
                        title="Asset Status Distribution"
                        data={[
                            { label: 'Active', count: assetStats.active || 0 },
                            { label: 'Maintenance', count: assetStats.maintenance || 0 },
                            { label: 'Disposed', count: assetStats.disposed || 0 },
                            { label: 'Inactive', count: (assetStats.total || 0) - (assetStats.active || 0) - (assetStats.maintenance || 0) - (assetStats.disposed || 0) }
                        ].filter(item => item.count > 0)}
                        defaultType="pie"
                    />
                    
                    {/* Asset Conditions Chart Visualization */}
                    {assetStats.conditions && Object.keys(assetStats.conditions).length > 0 && (
                        <ChartVisualization
                            title="Asset Condition Analysis"
                            data={Object.entries(assetStats.conditions).map(([condition, count]) => ({
                                label: condition.charAt(0).toUpperCase() + condition.slice(1),
                                count: count
                            }))}
                            defaultType="histogram"
                        />
                    )}
                </div>
            ) : (
                <div className="card" style={{marginBottom: '20px'}}>
                    <h2>IT Asset Summary</h2>
                    <div style={{textAlign: 'center', padding: '20px', color: '#64748b'}}>
                        <div style={{marginBottom: '12px'}}>
                            Failed to load asset statistics. This might be due to authentication issues.
                        </div>
                        <div style={{fontSize: '14px', marginBottom: '12px'}}>
                            Please make sure you are logged in and try again.
                        </div>
                        <button className="secondary" onClick={fetchAssetStats} style={{marginRight: '8px'}}>
                            Retry
                        </button>
                        <button className="primary" onClick={()=>navigate('/it-assets')}>
                            View Assets Directly
                        </button>
                    </div>
                </div>
            )}

            {/* Quick Actions */}
            <div className="card">
                <h2>Quick Actions</h2>
                <div className="top-actions">
                    {user && user.role === 'admin' && (
                        <button className="primary" onClick={()=>navigate('/it-assets')}>IT Asset Register</button>
                    )}
                    {user && user.role === 'admin' && (
                        <button className="primary" onClick={()=>navigate('/legal')}>Legal Management</button>
                    )}
                    {user && user.role === 'admin' && (
                        <button className="primary" onClick={()=>navigate('/admin')}>Admin Panel</button>
                    )}
                </div>
            </div>
        </div>
    )
}

export default Dashboard;
