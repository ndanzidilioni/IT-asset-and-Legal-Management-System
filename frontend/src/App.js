import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useLocation } from 'react-router-dom';
import Login from './components/Login';
import Dashboard from './components/Dashboard';
import TaskCreate from './components/TaskCreate';
import Register from './components/Register';
import NavBar from './components/NavBar';
import DeveloperPanel from './components/DeveloperPanel';
import ClientPanel from './components/ClientPanel';

// Reports and Admin Components
import InquiryReport from './components/InquiryReport';
import ITAssetReport from './components/ITAssetReport';
import AdminPanel from './components/AdminPanel';
import AuditLog from './components/AuditLog';
import SimpleTest from './components/SimpleTest';
import CascadingDropdownDemo from './components/CascadingDropdownDemo';

// Legal Management Components
import LegalDashboard from './components/Legal/LegalDashboard';
import CaseManagement from './components/Legal/CaseManagement';
import CreateCase from './components/Legal/CreateCase';
import EditCase from './components/Legal/EditCase';
import ViewCase from './components/Legal/ViewCase';
import ScheduleMeeting from './components/Legal/ScheduleMeeting';
import ContractRegister from './components/Legal/ContractRegister';
import CreateContract from './components/Legal/CreateContract';
import EditContract from './components/Legal/EditContract';
import ViewContract from './components/Legal/ViewContract';
import ClientManagement from './components/Legal/ClientManagement';
import CreateClient from './components/Legal/CreateClient';
import EditClient from './components/Legal/EditClient';
import BillingDashboard from './components/Legal/BillingDashboard';
import CourtSchedule from './components/Legal/CourtSchedule';
import LegalAnalytics from './components/Legal/LegalAnalytics';
import TestContracts from './components/Legal/TestContracts';
import DemandNotesDashboard from './components/Legal/DemandNotesDashboard';
import CreateDemandNote from './components/Legal/CreateDemandNote';
import EditDemandNote from './components/Legal/EditDemandNote';
import ViewDemandNote from './components/Legal/ViewDemandNote';
import CourtProceedingsForm from './components/Legal/CourtProceedingsForm';

function App(){
  const PrivateRoute = ({children}) => {
    const token = localStorage.getItem('token');
    return token ? children : <Navigate to="/login" />;
  };

  const RoleBasedRoute = ({children, allowedRoles}) => {
    const token = localStorage.getItem('token');
    const userInfo = localStorage.getItem('userInfo');
    
    if (!token) {
      return <Navigate to="/login" />;
    }
    
    if (!userInfo) {
      return <Navigate to="/login" />;
    }
    
    const user = JSON.parse(userInfo);
    
    if (!allowedRoles.includes(user.role)) {
      // Redirect to appropriate dashboard based on role
      switch(user.role) {
        case 'lawyer':
          return <Navigate to="/legal" />;
        case 'admin':
          return <Navigate to="/dashboard" />;
        case 'ict':
          return <Navigate to="/it-assets" />;
        case 'client':
          return <Navigate to="/client" />;
        default:
          return <Navigate to="/dashboard" />;
      }
    }
    
    return children;
  };

  const DefaultRoute = () => {
    const token = localStorage.getItem('token');
    const userInfo = localStorage.getItem('userInfo');
    
    if (!token) {
      return <Navigate to="/login" />;
    }
    
    if (!userInfo) {
      return <Navigate to="/login" />;
    }
    
    const user = JSON.parse(userInfo);
    
    // Redirect to appropriate dashboard based on role
    switch(user.role) {
      case 'lawyer':
        return <Navigate to="/legal" />;
      case 'admin':
        return <Navigate to="/dashboard" />;
      case 'ict':
        return <Navigate to="/it-assets" />;
      case 'client':
        return <Navigate to="/client" />;
      default:
        return <Navigate to="/dashboard" />;
    }
  };

  const AppContent = () => {
    const location = useLocation();
    const hideNavBar = ['/login', '/register'].includes(location.pathname);
    
    return (
      <>
        {!hideNavBar && <NavBar />}
        <Routes>
        <Route path="/" element={<DefaultRoute />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/test" element={<SimpleTest />} />
        <Route path="/cascading-demo" element={<PrivateRoute><CascadingDropdownDemo /></PrivateRoute>} />
        <Route path="/inquiry-report" element={<RoleBasedRoute allowedRoles={['admin']}><InquiryReport /></RoleBasedRoute>} />
        <Route path="/admin" element={<RoleBasedRoute allowedRoles={['admin']}><AdminPanel /></RoleBasedRoute>} />
        <Route path="/audit-logs" element={<RoleBasedRoute allowedRoles={['admin']}><AuditLog /></RoleBasedRoute>} />
        <Route path="/it-assets" element={<RoleBasedRoute allowedRoles={['admin', 'ict']}><ITAssetReport /></RoleBasedRoute>} />
        <Route path="/client" element={<RoleBasedRoute allowedRoles={['client']}><ClientPanel /></RoleBasedRoute>} />
        <Route path="/dashboard" element={<RoleBasedRoute allowedRoles={['admin']}><Dashboard /></RoleBasedRoute>} />
        <Route path="/create-task" element={<RoleBasedRoute allowedRoles={['admin']}><TaskCreate /></RoleBasedRoute>} />
        <Route path="/legal" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><LegalDashboard /></RoleBasedRoute>} />
        <Route path="/legal/cases" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><CaseManagement /></RoleBasedRoute>} />
        <Route path="/legal/cases/create" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><CreateCase /></RoleBasedRoute>} />
        <Route path="/legal/cases/edit/:id" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><EditCase /></RoleBasedRoute>} />
        <Route path="/legal/cases/view/:id" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><ViewCase /></RoleBasedRoute>} />
        <Route path="/legal/cases/:caseId/proceedings" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><CourtProceedingsForm /></RoleBasedRoute>} />
        <Route path="/legal/clients" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><ClientManagement /></RoleBasedRoute>} />
        <Route path="/legal/clients/create" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><CreateClient /></RoleBasedRoute>} />
        <Route path="/legal/clients/edit/:id" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><EditClient /></RoleBasedRoute>} />
        <Route path="/legal/schedule-meeting" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><ScheduleMeeting /></RoleBasedRoute>} />
        <Route path="/legal/contracts" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><ContractRegister /></RoleBasedRoute>} />
        <Route path="/legal/contracts/create" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><CreateContract /></RoleBasedRoute>} />
        <Route path="/legal/contracts/view/:id" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><ViewContract /></RoleBasedRoute>} />
        <Route path="/legal/contracts/edit/:id" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><EditContract /></RoleBasedRoute>} />
        <Route path="/legal/billing" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><BillingDashboard /></RoleBasedRoute>} />
        <Route path="/legal/court-schedule" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><CourtSchedule /></RoleBasedRoute>} />
        <Route path="/legal/analytics" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><LegalAnalytics /></RoleBasedRoute>} />
        <Route path="/legal/demand-notes" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><DemandNotesDashboard /></RoleBasedRoute>} />
        <Route path="/legal/demand-notes/create" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><CreateDemandNote /></RoleBasedRoute>} />
        <Route path="/legal/demand-notes/edit/:id" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><EditDemandNote /></RoleBasedRoute>} />
        <Route path="/legal/demand-notes/view/:id" element={<RoleBasedRoute allowedRoles={['lawyer', 'admin']}><ViewDemandNote /></RoleBasedRoute>} />
        <Route path="/test-contracts" element={<TestContracts />} />
        <Route path="*" element={<DefaultRoute />} />
        </Routes>
      </>
    );
  };

  return (
    <Router>
      <AppContent />
    </Router>
  );
}

export default App;
