import { createContext, useContext, ReactNode } from 'react';
import { User } from '@/types';
import { usePage, router } from '@inertiajs/react';
import { route } from 'ziggy-js';

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const { auth } = usePage<{ auth: { user: User | null } }>().props;

  const user = auth.user;
  const isLoading = false; // Inertia handles loading states via individual components or progress bar

  const logout = () => {
    router.post(route('logout'));
  };

  return (
    <AuthContext.Provider value={{ user, isLoading, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (context === undefined) {
    return {
      user: null,
      isLoading: false,
      logout: () => { },
    };
  }
  return context;
}