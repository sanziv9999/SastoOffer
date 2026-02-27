
import { useEffect } from 'react';
import HomePage from './HomePage';

const Index = () => {
  useEffect(() => {
    // Set page title
    document.title = 'Offer Oasis - Deal & Offer Aggregation Platform';
  }, []);

  return <HomePage />;
};

export default Index;
