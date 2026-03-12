import './bootstrap';
import '../css/app.css';
import './index.css';
import './App.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { AuthProvider } from "@/context/AuthContext";
import { TooltipProvider } from "@/components/ui/tooltip";
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";

import { BrowserRouter } from 'react-router-dom';
import RootLayout from '@/layouts/RootLayout';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
const queryClient = new QueryClient();

const publicPagesWithNavbar = ['HomePage', 'SearchPage', 'DealDetails', 'CheckoutPage', 'VendorProfile'];

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => {
    const page = resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx'));
    page.then((module: any) => {
      const existingLayout = module.default.layout as undefined | ((page: React.ReactNode) => React.ReactNode);

      const baseLayout =
        existingLayout ||
        ((p: React.ReactNode) => (publicPagesWithNavbar.includes(name) ? <RootLayout>{p}</RootLayout> : p));

      // Always provide AuthContext, even when pages define their own layout (DashboardLayout, RootLayout, etc.)
      module.default.layout = (p: React.ReactNode) => <AuthProvider>{baseLayout(p)}</AuthProvider>;
    });
    return page;
  },
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(
      <QueryClientProvider client={queryClient}>
        <TooltipProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <App {...props} />
          </BrowserRouter>
        </TooltipProvider>
      </QueryClientProvider>
    );
  },
  progress: {
    color: '#4B5563',
  },
});
