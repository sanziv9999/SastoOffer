import './bootstrap';
import '../css/app.css';
import './index.css';
import './App.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { AuthProvider } from "@/context/AuthContext";
import { TooltipProvider } from "@/components/ui/tooltip";
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";

import { BrowserRouter } from 'react-router-dom';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
const queryClient = new QueryClient();

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => {
    const page = resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx'));
    page.then((module: any) => {
      module.default.layout = module.default.layout || ((page: any) => <AuthProvider>{page}</AuthProvider>);
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
