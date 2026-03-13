import { BrowserRouter as Router, Routes, Route, Navigate, Outlet } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { Suspense, lazy } from 'react';

// Lazy load pages for better performance
import { AuthProvider, useAuth } from './context/AuthContext';
import Layout from './components/Layout';
import ErrorBoundary from './components/ErrorBoundary';
import SubscriptionOverlay from './components/system/SubscriptionOverlay';
import PlanLimitBanner from './components/system/PlanLimitBanner';
import SubscriptionWarningBanner from './components/system/SubscriptionWarningBanner';
import ImpersonationBanner from './components/system/ImpersonationBanner';

const Login = lazy(() => import('./pages/Login'));
const Dashboard = lazy(() => import('./pages/Dashboard'));
const Patients = lazy(() => import('./pages/Patients'));
const PatientDetails = lazy(() => import('./pages/PatientDetails'));
const Appointments = lazy(() => import('./pages/Appointments'));
const Billing = lazy(() => import('./pages/Billing'));
const ExpenseManager = lazy(() => import('./pages/Expenses'));
const Inventory = lazy(() => import('./pages/Inventory'));
const GrowthInsights = lazy(() => import('./pages/Insights'));
const ClinicalSettings = lazy(() => import('./pages/Settings'));
const ClinicalServices = lazy(() => import('./pages/ClinicalServices'));
const UserProfile = lazy(() => import('./pages/UserProfile'));
const FinanceDashboard = lazy(() => import('./pages/finance/Dashboard'));

const AccessDenied = lazy(() => import('./pages/AccessDenied'));
const PublicInvoice = lazy(() => import('./pages/PublicInvoice'));

// Admin Pages
const DoctorManagement = lazy(() => import('./pages/admin/DoctorManagement'));
const PlanManagement = lazy(() => import('./pages/admin/PlanManagement'));
const SystemSettings = lazy(() => import('./pages/admin/SystemSettings'));
const CatalogManager = lazy(() => import('./pages/admin/CatalogManager'));
const PharmacyCatalog = lazy(() => import('./pages/admin/PharmacyCatalog'));
const LandingEnquiries = lazy(() => import('./pages/admin/LandingEnquiries'));
const SystemGovernance = lazy(() => import('./pages/admin/SystemGovernance'));

const ProtectedRoute = ({ children, allowedRoles, restrictedRoleTypes }) => {
  const { user, authenticated, loading } = useAuth();

  if (loading) return <div className="flex h-screen items-center justify-center">Authenticating...</div>;

  if (!authenticated) return <Navigate to="/login" replace />;

  if (allowedRoles && !allowedRoles.includes(user?.role)) {
    return <Navigate to="/access-denied" replace />;
  }

  // Staff specific permission checks
  if (user?.role === 'staff' && restrictedRoleTypes) {
    if (restrictedRoleTypes.includes(user?.role_type)) {
      return <Navigate to="/access-denied" replace />;
    }
  }

  return children ? children : <Outlet />;
};

import { SubscriptionProvider } from './context/SubscriptionContext';

function App() {
  return (
    <Router basename="/app">
      <AuthProvider>
        <SubscriptionProvider>
          <>
            <SubscriptionOverlay />
            <SubscriptionWarningBanner />
            <PlanLimitBanner />
            <ImpersonationBanner />

            <Toaster position="top-right" />
            <ErrorBoundary>
              <Suspense fallback={<div className="flex h-screen w-screen items-center justify-center">Loading...</div>}>
                <Routes>
                  <Route path="/login" element={<Login />} />
                  <Route path="/access-denied" element={<AccessDenied />} />

                  {/* Authenticated Routes wrapped in Layout */}
                  <Route element={<ProtectedRoute />}>
                    <Route element={<Layout />}>
                      <Route path="/" element={<Dashboard />} />
                      <Route path="/profile" element={<UserProfile />} />

                      {/* Patient Management - Accessible to All Staff */}
                      <Route path="patients" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']}>
                          <Patients />
                        </ProtectedRoute>
                      } />
                      <Route path="patients/:id" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']}>
                          <PatientDetails />
                        </ProtectedRoute>
                      } />
                      <Route path="appointments" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']}>
                          <Appointments />
                        </ProtectedRoute>
                      } />

                      {/* Billing - Accessible to All, but Receptionist is Read-Only (Handled in Page) */}
                      <Route path="billing" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']}>
                          <Billing />
                        </ProtectedRoute>
                      } />

                      {/* Expenses - Assistant & Doctor Only (Receptionist Restricted) */}
                      <Route path="expenses" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']} restrictedRoleTypes={['receptionist']}>
                          <ExpenseManager />
                        </ProtectedRoute>
                      } />

                      {/* Inventory - Assistant & Doctor Only */}
                      <Route path="inventory" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']} restrictedRoleTypes={['receptionist']}>
                          <Inventory />
                        </ProtectedRoute>
                      } />

                      {/* Insights - Assistant & Doctor Only */}
                      <Route path="insights" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']} restrictedRoleTypes={['receptionist']}>
                          <GrowthInsights />
                        </ProtectedRoute>
                      } />

                      <Route path="finance/dashboard" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']} restrictedRoleTypes={['receptionist']}>
                          <FinanceDashboard />
                        </ProtectedRoute>
                      } />

                      {/* Clinical Services - Assistant & Doctor Only */}
                      <Route path="services" element={
                        <ProtectedRoute allowedRoles={['doctor', 'staff']} restrictedRoleTypes={['receptionist']}>
                          <ClinicalServices />
                        </ProtectedRoute>
                      } />

                      {/* Settings - Doctor Only (Staff Restricted) */}
                      <Route path="settings" element={
                        <ProtectedRoute allowedRoles={['doctor']}>
                          <ClinicalSettings />
                        </ProtectedRoute>
                      } />

                      {/* Super Admin ONLY Routes */}
                      <Route path="admin/doctors" element={<ProtectedRoute allowedRoles={['super_admin']}><DoctorManagement /></ProtectedRoute>} />
                      <Route path="admin/plans" element={<ProtectedRoute allowedRoles={['super_admin']}><PlanManagement /></ProtectedRoute>} />
                      <Route path="admin/settings" element={<ProtectedRoute allowedRoles={['super_admin']}><SystemSettings /></ProtectedRoute>} />
                      <Route path="admin/catalog" element={<ProtectedRoute allowedRoles={['super_admin']}><CatalogManager /></ProtectedRoute>} />
                      <Route path="admin/pharmacy" element={<ProtectedRoute allowedRoles={['super_admin']}><PharmacyCatalog /></ProtectedRoute>} />
                      <Route path="admin/enquiries" element={<ProtectedRoute allowedRoles={['super_admin']}><LandingEnquiries /></ProtectedRoute>} />
                      <Route path="admin/governance" element={<ProtectedRoute allowedRoles={['super_admin']}><SystemGovernance /></ProtectedRoute>} />
                    </Route>
                  </Route>

                  <Route path="/invoice/:uuid" element={<PublicInvoice />} />
                  <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
              </Suspense>
            </ErrorBoundary>
          </>
        </SubscriptionProvider>
      </AuthProvider>
    </Router>
  );
}

export default App;
