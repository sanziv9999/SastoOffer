import { Routes, Route } from "react-router-dom";
import HomePage from "./HomePage";
import SearchPage from "./SearchPage";
import LoginPage from "./LoginPage";
import RegisterPage from "./RegisterPage";
import ForgotPasswordPage from "./ForgotPasswordPage";
import DealDetails from "./DealDetails";
import CheckoutPage from "./CheckoutPage";
import VendorProfile from "./VendorProfile";
import CustomerDashboard from "./CustomerDashboard";
import VendorDashboard from "./VendorDashboard";
import AdminDashboard from "./AdminDashboard";
import SuperAdminDashboard from "./SuperAdminDashboard";
import NotFound from "./NotFound";
import DashboardLayout from "@/layouts/DashboardLayout";

// Dummy data for Dashboards
const dummyStats = {
    customer: {
        totalPurchases: 5,
        activeCoupons: 2,
        totalSavings: 45.50,
        favoriteDealsCount: 12
    },
    vendor: {
        totalRevenue: 1250.00,
        totalSales: 45,
        activeDeals: 8,
        totalDeals: 15
    },
    admin: {
        totalRevenue: 15420.00,
        revenueChange: "+15%",
        totalUsers: 1250,
        totalVendors: 85,
        activeDealsCount: 342,
        totalSales: 1540,
        redeemedSalesCount: 1200
    },
    superAdmin: {
        totalRevenue: 154200.00,
        platformCommission: 15420.00,
        totalUsers: 12500,
        userGrowth: 450,
        totalVendors: 850,
        totalAdmins: 12,
        systemUptime: "99.9%"
    }
};

const AppShell = () => {
    return (
        <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/search" element={<SearchPage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPasswordPage />} />
            <Route path="/deal/:id" element={<DealDetails />} />
            <Route path="/checkout" element={<CheckoutPage />} />
            <Route path="/vendor/:id" element={<VendorProfile />} />

            {/* Dashboard Routes with Layout */}
            <Route element={<DashboardLayout children={undefined} />}>
                <Route path="/dashboard/*" element={
                    <CustomerDashboard
                        stats={dummyStats.customer}
                        recommendations={[]}
                        recentActivity={[]}
                        deals={[]}
                    />
                } />
                <Route path="/vendor/*" element={
                    <VendorDashboard
                        vendor={{ businessName: 'Test Vendor', averageRating: 4.5 }}
                        stats={dummyStats.vendor}
                        deals={[]}
                    />
                } />
                <Route path="/admin/*" element={
                    <AdminDashboard
                        stats={dummyStats.admin}
                        pendingDeals={[]}
                        recentUsers={[]}
                        vendorsList={[]}
                        systemAlerts={[]}
                    />
                } />
                <Route path="/super-admin/*" element={
                    <SuperAdminDashboard
                        stats={dummyStats.superAdmin}
                        databaseStatus={{ storageUsed: '45GB', storageLimit: '100GB', activeConnections: 12, connectionLimit: 100, performance: 'Optimal' }}
                        securityStatus={{ failedLogins: 2, sslValid: true, firewallActive: true }}
                        paymentStatus={{ todayTransactions: 156, successRate: '98%', gatewayStatus: 'Online' }}
                        adminsList={[]}
                    />
                } />
            </Route>

            <Route path="*" element={<NotFound />} />
        </Routes>
    );
};

export default AppShell;
