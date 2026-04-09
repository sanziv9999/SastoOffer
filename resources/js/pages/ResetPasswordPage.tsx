import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { ArrowLeft, Lock } from 'lucide-react';
import Logo from '@/components/Logo';

type Props = {
  token?: string;
  email?: string;
};

const ResetPasswordPage = ({ token = '', email = '' }: Props) => {
  const { toast } = useToast();
  const [form, setForm] = useState({
    token,
    email,
    password: '',
    password_confirmation: '',
  });
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!form.email || !form.password || !form.password_confirmation) {
      toast({ title: 'Error', description: 'Please fill all fields.', variant: 'destructive' });
      return;
    }

    try {
      setIsLoading(true);
      await (window as any).axios.post('/reset-password', form);
      toast({ title: 'Password updated', description: 'Your password has been reset. Please sign in.' });
      window.location.href = '/login';
    } catch (error: any) {
      const message =
        error?.response?.data?.errors?.email?.[0] ||
        error?.response?.data?.errors?.password?.[0] ||
        error?.response?.data?.message ||
        'Unable to reset password. The link may be invalid or expired.';
      toast({ title: 'Reset failed', description: message, variant: 'destructive' });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-muted/30">
      <div className="w-full max-w-md mb-6">
        <Link to="/login" className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary transition-colors">
          <ArrowLeft className="h-4 w-4" />
          Back to Sign In
        </Link>
      </div>

      <Card className="w-full max-w-md">
        <CardHeader className="space-y-3 text-center">
          <a href="/" className="flex justify-center mb-2">
            <Logo />
          </a>
          <CardTitle className="text-2xl font-bold">Create New Password</CardTitle>
          <CardDescription>Enter a new password for your account.</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-1.5">
              <label htmlFor="email" className="text-sm font-medium">Email Address</label>
              <Input
                id="email"
                type="email"
                value={form.email}
                onChange={(e) => setForm((prev) => ({ ...prev, email: e.target.value }))}
                required
              />
            </div>

            <div className="space-y-1.5">
              <label htmlFor="password" className="text-sm font-medium">New Password</label>
              <div className="relative">
                <Lock className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  id="password"
                  type="password"
                  className="pl-10"
                  value={form.password}
                  onChange={(e) => setForm((prev) => ({ ...prev, password: e.target.value }))}
                  required
                />
              </div>
            </div>

            <div className="space-y-1.5">
              <label htmlFor="password_confirmation" className="text-sm font-medium">Confirm Password</label>
              <Input
                id="password_confirmation"
                type="password"
                value={form.password_confirmation}
                onChange={(e) => setForm((prev) => ({ ...prev, password_confirmation: e.target.value }))}
                required
              />
            </div>

            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading ? 'Resetting...' : 'Reset Password'}
            </Button>
          </form>
        </CardContent>
        <CardFooter className="justify-center">
          <p className="text-sm text-muted-foreground">
            Remember your password?{' '}
            <Link to="/login" className="text-primary font-semibold hover:underline">
              Sign In
            </Link>
          </p>
        </CardFooter>
      </Card>
    </div>
  );
};

export default ResetPasswordPage;

