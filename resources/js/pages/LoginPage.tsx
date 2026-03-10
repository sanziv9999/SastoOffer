
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Facebook, Apple, ArrowLeft } from 'lucide-react';
import Logo from '@/components/Logo';
import { route } from 'ziggy-js';
import { Link, useForm } from '@inertiajs/react';

const LoginPage = () => {
  const { data, setData, post, processing, errors, reset } = useForm({
    email: '',
    password: '',
    remember: false,
  });
  const [showSignUpHighlight, setShowSignUpHighlight] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    post(route('login'), {
      onFinish: () => reset('password'),
    });
  };

  const handleSocialLogin = (provider: string) => {
    alert(`${provider} login coming soon!`);
  };

  const fillCredentials = (email: string) => {
    setData('email', email);
    setData('password', 'password');
  };

  return (
    <div className="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-muted/30">
      {/* Back to home */}
      <div className="w-full max-w-md mb-6">
        <Link href="/" className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary transition-colors">
          <ArrowLeft className="h-4 w-4" />
          Back to Home
        </Link>
      </div>

      <Card className="w-full max-w-md">
        <CardHeader className="space-y-3 text-center">
          <Link href="/" className="flex justify-center mb-2">
            <Logo />
          </Link>
          <CardTitle className="text-2xl font-bold">Sign In</CardTitle>
          <CardDescription>
            Enter your email and password to access your account
          </CardDescription>
        </CardHeader>
        <CardContent>
          {/* Test Credentials */}
          <div className="mb-5 p-3 bg-muted rounded-lg border border-border">
            <p className="text-xs font-semibold text-muted-foreground mb-2 uppercase tracking-wide">Quick Login (Demo)</p>
            <div className="grid grid-cols-2 gap-2">
              <Button type="button" variant="outline" size="sm" className="text-xs h-auto py-2 flex-col items-start" onClick={() => fillCredentials('admin@sastooffer.test')}>
                <span className="font-semibold">Admin</span>
                <span className="text-muted-foreground text-[10px]">admin@sastooffer.test</span>
              </Button>
              <Button type="button" variant="outline" size="sm" className="text-xs h-auto py-2 flex-col items-start" onClick={() => fillCredentials('vendor@sastooffer.test')}>
                <span className="font-semibold">Vendor</span>
                <span className="text-muted-foreground text-[10px]">vendor@sastooffer...</span>
              </Button>
              <Button type="button" variant="outline" size="sm" className="text-xs h-auto py-2 flex-col items-start" onClick={() => fillCredentials('customer@sastooffer.test')}>
                <span className="font-semibold">Customer</span>
                <span className="text-muted-foreground text-[10px]">customer@sastooffer...</span>
              </Button>
              <Button type="button" variant="outline" size="sm" className="text-xs h-auto py-2 flex-col items-start" onClick={() => fillCredentials('superadmin@sastooffer.test')}>
                <span className="font-semibold">Super Admin</span>
                <span className="text-muted-foreground text-[10px]">superadmin@sastooffer...</span>
              </Button>
            </div>
          </div>

          {/* Social Login */}
          <div className="grid grid-cols-3 gap-2 mb-5">
            <Button variant="outline" size="sm" className="w-full" onClick={() => handleSocialLogin('Google')}>
              <svg className="w-4 h-4 mr-1.5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
              </svg>
              Google
            </Button>
            <Button variant="outline" size="sm" className="w-full" onClick={() => handleSocialLogin('Facebook')}>
              <Facebook className="h-4 w-4 mr-1.5 text-blue-600" />
              Facebook
            </Button>
            <Button variant="outline" size="sm" className="w-full" onClick={() => handleSocialLogin('Apple')}>
              <Apple className="h-4 w-4 mr-1.5" />
              Apple
            </Button>
          </div>

          <div className="relative mb-5">
            <Separator />
            <div className="absolute inset-0 flex items-center justify-center">
              <span className="bg-card px-2 text-sm text-muted-foreground">OR</span>
            </div>
          </div>

          <form onSubmit={handleSubmit}>
            <div className="space-y-4">
              <div className="space-y-1.5">
                <label htmlFor="email" className="text-sm font-medium">Email</label>
                <Input
                  id="email"
                  type="email"
                  placeholder="name@example.com"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  required
                  className={errors.email ? 'border-destructive' : ''}
                />
                {errors.email && <p className="text-xs text-destructive">{errors.email}</p>}
              </div>
              <div className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <label htmlFor="password" className="text-sm font-medium">Password</label>
                  <Link href="/forgot-password" disable-nprogress="true" className="text-xs text-primary hover:underline">
                    Forgot password?
                  </Link>
                </div>
                <Input
                  id="password"
                  type="password"
                  placeholder="••••••••"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  required
                  className={errors.password ? 'border-destructive' : ''}
                />
                {errors.password && <p className="text-xs text-destructive">{errors.password}</p>}
              </div>
              <Button type="submit" className="w-full" disabled={processing}>
                {processing ? "Signing in..." : "Sign In"}
              </Button>
            </div>
          </form>
        </CardContent>
        <CardFooter className="flex-col gap-3">
          <div className={`text-center p-3 rounded-lg w-full transition-all ${showSignUpHighlight ? 'bg-primary/10 border border-primary/30' : ''}`}>
            <p className="text-sm text-muted-foreground">
              Don't have an account?{' '}
              <Link
                href={route('register')}
                className="text-primary font-semibold hover:underline"
                onMouseEnter={() => setShowSignUpHighlight(true)}
                onMouseLeave={() => setShowSignUpHighlight(false)}
              >
                Sign Up
              </Link>
            </p>
          </div>
        </CardFooter>
      </Card>
    </div>
  );
};


export default LoginPage;
