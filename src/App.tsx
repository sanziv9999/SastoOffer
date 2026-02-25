
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";

// Pages
import HomePage from "./pages/HomePage";
import DealDetails from "./pages/DealDetails";
import CheckoutPage from "./pages/CheckoutPage";
import LoginPage from "./pages/LoginPage";
import RegisterPage from "./pages/RegisterPage";
import ForgotPasswordPage from "./pages/ForgotPasswordPage";
import UserDashboard from "./pages/UserDashboard";
import CustomerDashboard from "./pages/CustomerDashboard";
import VendorDashboard from "./pages/VendorDashboard";
import AdminDashboard from "./pages/AdminDashboard";
import SuperAdminDashboard from "./pages/SuperAdminDashboard";
import SearchPage from "./pages/SearchPage";
import NotFound from "./pages/NotFound";

// Dashboard sub-pages
import SavedDeals from "./pages/dashboard/SavedDeals";
import MyPurchases from "./pages/dashboard/MyPurchases";
import Reviews from "./pages/dashboard/Reviews";
import Notifications from "./pages/dashboard/Notifications";
import Settings from "./pages/dashboard/Settings";

// Vendor sub-pages
import CreateDeal from "./pages/vendor/CreateDeal";
import ManageDeals from "./pages/vendor/ManageDeals";
import VendorAnalytics from "./pages/vendor/Analytics";
import VendorCustomers from "./pages/vendor/Customers";
import VendorCustomerHistory from "./pages/vendor/CustomerHistory";
import VendorSalesHistory from "./pages/vendor/SalesHistory";
import VendorOrders from "./pages/vendor/Orders";

// Admin sub-pages
import AdminUsers from "./pages/admin/AdminUsers";
import AdminVendors from "./pages/admin/AdminVendors";
import AdminDeals from "./pages/admin/AdminDeals";
import AdminReports from "./pages/admin/AdminReports";

// Layout
import RootLayout from "./layouts/RootLayout";
import DashboardLayout from "./layouts/DashboardLayout";
import ProtectedRoute from "./components/ProtectedRoute";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <AuthProvider>
        <Toaster />
        <Sonner />
        <BrowserRouter>
          <Routes>
            <Route path="/" element={<RootLayout />}>
              <Route index element={<HomePage />} />
              <Route path="deals/:id" element={<DealDetails />} />
              <Route path="checkout/:id" element={<CheckoutPage />} />
              <Route path="search" element={<SearchPage />} />
              <Route path="register" element={<RegisterPage />} />
            </Route>

            <Route path="login" element={<LoginPage />} />
            <Route path="forgot-password" element={<ForgotPasswordPage />} />

            {/* Customer Dashboard */}
            <Route path="dashboard" element={
              <ProtectedRoute allowedRoles={['user', 'vendor', 'admin', 'super_admin']}>
                <DashboardLayout />
              </ProtectedRoute>
            }>
              <Route index element={<CustomerDashboard />} />
              <Route path="favorites" element={<SavedDeals />} />
              <Route path="purchases" element={<MyPurchases />} />
              <Route path="reviews" element={<Reviews />} />
              <Route path="notifications" element={<Notifications />} />
              <Route path="settings" element={<Settings />} />
            </Route>

            {/* Vendor Dashboard */}
            <Route path="vendor" element={
              <ProtectedRoute allowedRoles={['vendor', 'admin', 'super_admin']}>
                <DashboardLayout />
              </ProtectedRoute>
            }>
              <Route index element={<VendorDashboard />} />
              <Route path="create-deal" element={<CreateDeal />} />
              <Route path="deals" element={<ManageDeals />} />
              <Route path="analytics" element={<VendorAnalytics />} />
              <Route path="customers" element={<VendorCustomers />} />
              <Route path="customer-history" element={<VendorCustomerHistory />} />
              <Route path="sales-history" element={<VendorSalesHistory />} />
              <Route path="orders" element={<VendorOrders />} />
            </Route>

            {/* Admin Dashboard */}
            <Route path="admin" element={
              <ProtectedRoute allowedRoles={['admin', 'super_admin']}>
                <DashboardLayout />
              </ProtectedRoute>
            }>
              <Route index element={<AdminDashboard />} />
              <Route path="users" element={<AdminUsers />} />
              <Route path="vendors" element={<AdminVendors />} />
              <Route path="deals" element={<AdminDeals />} />
              <Route path="reports" element={<AdminReports />} />
            </Route>

            <Route path="super-admin" element={
              <ProtectedRoute allowedRoles={['super_admin']}>
                <DashboardLayout />
              </ProtectedRoute>
            }>
              <Route index element={<SuperAdminDashboard />} />
            </Route>
            
            <Route path="*" element={<NotFound />} />
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
