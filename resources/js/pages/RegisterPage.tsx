import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Facebook, Apple, ArrowLeft } from 'lucide-react';
import { Checkbox } from '@/components/ui/checkbox';
import Logo from '@/components/Logo';
import { Link, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';

const RegisterPage = () => {
  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'customer' as 'customer' | 'vendor',
    terms: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    post(route('register'), {
      onFinish: () => reset('password', 'password_confirmation'),
    });
  };

  const handleSocialRegister = (provider: string) => {
    alert(`${provider} signup coming soon!`);
  };

  return (
    <div className="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-muted/30">
      {/* Back to home */}
      <div className="w-full max-w-md mb-6">
        <a href="/" className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary transition-colors">
          <ArrowLeft className="h-4 w-4" />
          Back to Home
        </a>
      </div>
      <Card className="w-full max-w-md">
        <CardHeader className="space-y-1 text-center">
          <a href="/" className="flex justify-center mb-2">
            <Logo />
          </a>
          <CardTitle className="text-2xl font-bold">Create an account</CardTitle>
          <CardDescription className="text-center">
            Enter your details to create your account
          </CardDescription>
        </CardHeader>
        <CardContent>
          {/* Social Login Options */}
          <div className="grid grid-cols-3 gap-3 mb-6">
            <Button
              variant="outline"
              className="w-full"
              onClick={() => handleSocialRegister('Google')}
            >
              <svg className="w-5 h-5 mr-2" viewBox="0 0 24 24">
                <path
                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                  fill="#4285F4"
                />
                <path
                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                  fill="#34A853"
                />
                <path
                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                  fill="#FBBC05"
                />
                <path
                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                  fill="#EA4335"
                />
              </svg>
              Google
            </Button>
            <Button
              variant="outline"
              className="w-full"
              onClick={() => handleSocialRegister('Facebook')}
            >
              <Facebook className="h-5 w-5 mr-2 text-blue-600" />
              Facebook
            </Button>
            <Button
              variant="outline"
              className="w-full"
              onClick={() => handleSocialRegister('Apple')}
            >
              <Apple className="h-5 w-5 mr-2" />
              Apple
            </Button>
          </div>

          <div className="relative mb-6">
            <Separator />
            <div className="absolute inset-0 flex items-center justify-center">
              <span className="bg-white px-2 text-sm text-gray-500">OR</span>
            </div>
          </div>

          <form onSubmit={handleSubmit}>
            <div className="space-y-4">
              <div className="space-y-2">
                <label className="text-sm font-medium">I want to register as a</label>
                <div className="grid grid-cols-2 gap-4">
                  <Button
                    type="button"
                    variant={data.role === 'customer' ? 'default' : 'outline'}
                    onClick={() => setData('role', 'customer')}
                    className="w-full"
                  >
                    Customer
                  </Button>
                  <Button
                    type="button"
                    variant={data.role === 'vendor' ? 'default' : 'outline'}
                    onClick={() => setData('role', 'vendor')}
                    className="w-full"
                  >
                    Vendor
                  </Button>
                </div>
                {errors.role && <p className="text-xs text-destructive">{errors.role}</p>}
              </div>
              <div className="space-y-2">
                <label htmlFor="name" className="text-sm font-medium">Name</label>
                <Input
                  id="name"
                  type="text"
                  placeholder="John Doe"
                  value={data.name}
                  onChange={(e) => setData('name', e.target.value)}
                  required
                  className={errors.name ? 'border-destructive' : ''}
                />
                {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
              </div>
              <div className="space-y-2">
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
              <div className="space-y-2">
                <label htmlFor="password" className="text-sm font-medium">Password</label>
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
              <div className="space-y-2">
                <label htmlFor="password_confirmation" className="text-sm font-medium">Confirm Password</label>
                <Input
                  id="password_confirmation"
                  type="password"
                  placeholder="••••••••"
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  required
                  className={errors.password_confirmation ? 'border-destructive' : ''}
                />
                {errors.password_confirmation && <p className="text-xs text-destructive">{errors.password_confirmation}</p>}
              </div>
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="terms"
                  checked={data.terms}
                  onCheckedChange={(checked) => setData('terms', checked as boolean)}
                />
                <label
                  htmlFor="terms"
                  className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                >
                  I accept the{' '}
                  <Link href="/terms" className="text-primary hover:underline">
                    terms and conditions
                  </Link>
                </label>
              </div>
              {errors.terms && <p className="text-xs text-destructive">{errors.terms}</p>}
              <Button type="submit" className="w-full" disabled={processing}>
                {processing ? "Creating account..." : "Create account"}
              </Button>
            </div>
          </form>
        </CardContent>
        <CardFooter className="justify-center">
          <p className="text-sm text-gray-600">
            Already have an account?{' '}
            <Link href={route('login')} className="text-primary hover:underline">
              Sign in
            </Link>
          </p>
        </CardFooter>
      </Card>
    </div>
  );
};

export default RegisterPage;
