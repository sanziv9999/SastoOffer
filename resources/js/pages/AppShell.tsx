import { Routes, Route } from "react-router-dom";
import { useEffect } from "react";
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
import RootLayout from "@/layouts/RootLayout";

// Dashboard Sub-pages (Customer)
import MyPurchases from "./dashboard/MyPurchases";
import Notifications from "./dashboard/Notifications";
import Reviews from "./dashboard/Reviews";
import SavedDeals from "./dashboard/SavedDeals";
import Settings from "./dashboard/Settings";
import EditReview from "./dashboard/EditReview";
import VoucherDetail from "./dashboard/VoucherDetail";

// Dashboard Sub-pages (Vendor)
import VendorAnalytics from "./vendor/Analytics";
import VendorCreateDeal from "./vendor/CreateDeal";
import VendorCustomerHistory from "./vendor/CustomerHistory";
import VendorCustomers from "./vendor/Customers";
import VendorManageDeals from "./vendor/ManageDeals";
import VendorOrders from "./vendor/Orders";
import VendorSalesHistory from "./vendor/SalesHistory";
import VendorScanner from "./vendor/Scanner";
import VendorReviews from "./vendor/Reviews";
import VendorSettings from "./vendor/Settings";
import VendorInventory from "./vendor/Inventory";
import VendorInsights from "./vendor/Insights";
import VendorNotifications from "./vendor/Notifications";

// Dashboard Sub-pages (Admin)
import AdminDeals from "./admin/AdminDeals";
import AdminPendingDeals from "./admin/AdminPendingDeals";
import AdminReports from "./admin/AdminReports";
import AdminRevenueReports from "./admin/AdminRevenueReports";
import AdminUserAnalytics from "./admin/AdminUserAnalytics";
import AdminUsers from "./admin/AdminUsers";
import AdminVendors from "./admin/AdminVendors";

// Mock Data
import { deals, purchases, reviews, users, vendors, notifications, vendorOrders, vendorCustomers } from "@/data/mockData";
import { useAuth } from "@/context/AuthContext";

// Dummy stats for Dashboards
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
        totalDeals: 15,
        avgOrderValue: 27.78,
        pageViews: 1250,
        conversionRate: 3.6,
        revenueChange: "+12%",
        salesChange: "+5%",
        aovChange: "+2%",
        viewsChange: "+10%",
        conversionChange: "+1.2%",
        activeDealsCount: 8
    },
    admin: {
        totalRevenue: 15420.00,
        totalSales: 1540,
        totalUsers: 1250,
        totalVendors: 85,
        activeDeals: 342,
        conversionRate: "2.4%",
        revenueChange: "+15%",
        salesChange: "+10%",
        usersChange: "+5%",
        vendorsChange: "+2%",
        dealsChange: "+8%",
        conversionChange: "+0.5%"
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
    const { user } = useAuth();

    // Auto-login a test user for prototype if no user is found 
    // and they haven't explicitly logged out in this session.
    useEffect(() => {
        const hasLoggedOut = sessionStorage.getItem('loggedOut');
        const savedUser = localStorage.getItem('user');

        if (!user && !savedUser && !hasLoggedOut) {
            // Default to a regular customer (John Doe) for prototype
            const testUser = users.find(u => u.email === 'john@example.com') || users[0];
            if (testUser) {
                localStorage.setItem('user', JSON.stringify(testUser));
                window.location.reload();
            }
        }
    }, [user]);

    const activeVendor = vendors[0];
    const vendorDeals = deals.filter(d => d.vendorId === activeVendor.id);

    return (
        <Routes>
            {/* Auth pages WITHOUT global Navbar/Footer */}
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPasswordPage />} />

            {/* Site pages with RootLayout (Navbar, Footer) */}
            <Route element={<RootLayout />}>
                <Route path="/" element={<HomePage />} />
                <Route path="/search" element={<SearchPage />} />
                <Route path="/deal/:id" element={<DealDetails />} />
                <Route path="/checkout" element={<CheckoutPage />} />
                <Route path="/vendor-profile/:id" element={<VendorProfile />} />
            </Route>

            {/* Dashboard routes with DashboardLayout (Sidebar) */}
            <Route element={<DashboardLayout children={undefined} />}>
                {/* Customer Dashboard */}
                <Route path="/dashboard">
                    <Route index element={
                        <CustomerDashboard
                            stats={dummyStats.customer}
                            recommendations={deals.slice(0, 3)}
                            recentActivity={purchases.slice(0, 5)}
                            deals={deals}
                        />
                    } />
                    <Route path="favorites" element={<SavedDeals favoriteDeals={deals.slice(0, 2)} />} />
                    <Route path="purchases" element={<MyPurchases purchases={purchases} deals={deals} />} />
                    <Route path="purchases/:id" element={<VoucherDetail purchases={purchases} deals={deals} vendors={vendors} />} />
                    <Route path="reviews" element={<Reviews reviews={reviews} deals={deals} />} />
                    <Route path="reviews/edit/:id" element={<EditReview reviews={reviews} deals={deals} />} />
                    <Route path="notifications" element={<Notifications notifications={notifications} />} />
                    <Route path="settings" element={<Settings />} />
                </Route>

                {/* Vendor Dashboard */}
                <Route path="/vendor">
                    <Route index element={
                        <VendorDashboard
                            vendor={activeVendor}
                            stats={dummyStats.vendor}
                            deals={vendorDeals}
                        />
                    } />
                    <Route path="create-deal" element={<VendorCreateDeal />} />
                    <Route path="deals" element={<VendorManageDeals deals={vendorDeals} />} />
                    <Route path="orders" element={<VendorOrders orders={vendorOrders} />} />
                    <Route path="analytics" element={<VendorAnalytics stats={dummyStats.vendor} topDeals={vendorDeals.slice(0, 5)} />} />
                    <Route path="customers" element={<VendorCustomers customers={vendorCustomers} />} />
                    <Route path="customer-history" element={<VendorCustomerHistory history={[]} />} />
                    <Route path="sales-history" element={<VendorSalesHistory sales={[]} />} />
                    <Route path="scanner" element={<VendorScanner />} />
                    <Route path="reviews" element={<VendorReviews reviews={reviews} deals={vendorDeals} />} />
                    <Route path="settings" element={<VendorSettings />} />
                    <Route path="inventory" element={<VendorInventory deals={vendorDeals} />} />
                    <Route path="insights" element={<VendorInsights />} />
                    <Route path="notifications" element={<VendorNotifications />} />
                </Route>

                {/* Admin Dashboard */}
                <Route path="/admin">
                    <Route index element={
                        <AdminDashboard
                            stats={dummyStats.admin}
                            pendingDeals={deals.slice(0, 3)}
                            recentUsers={users.slice(0, 5)}
                            vendorsList={vendors}
                            systemAlerts={[
                                { title: 'Server Load High', description: 'System performance might be affected.', type: 'warning' }
                            ]}
                        />
                    } />
                    <Route path="users" element={<AdminUsers users={users} />} />
                    <Route path="vendors" element={<AdminVendors vendors={vendors} />} />
                    <Route path="deals" element={<AdminDeals deals={deals} />} />
                    <Route path="deals/pending" element={<AdminPendingDeals pendingDeals={deals.slice(0, 3)} />} />
                    <Route path="reports" element={<AdminReports statsData={dummyStats.admin} />} />
                    <Route path="reports/revenue" element={<AdminRevenueReports />} />
                    <Route path="reports/users" element={<AdminUserAnalytics />} />
                </Route>

                {/* Super Admin Dashboard */}
                <Route path="/super-admin">
                    <Route index element={
                        <SuperAdminDashboard
                            stats={dummyStats.superAdmin}
                            databaseStatus={{ storageUsed: '45GB', storageLimit: '100GB', activeConnections: 12, connectionLimit: 100, performance: 'Optimal' }}
                            securityStatus={{ failedLogins: 2, sslValid: true, firewallActive: true }}
                            paymentStatus={{ todayTransactions: 156, successRate: '98%', gatewayStatus: 'Online' }}
                            adminsList={users.filter(u => u.role === 'admin')}
                        />
                    } />
                    <Route path="admins" element={<div>Admin Management Page</div>} />
                    <Route path="analytics" element={<div>System Analytics Page</div>} />
                </Route>
            </Route>

            <Route path="*" element={<NotFound />} />
        </Routes>
    );
};

export default AppShell;
