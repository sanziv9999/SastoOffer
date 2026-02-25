
import { useState, useEffect, useRef } from 'react';
import { Search, Tag } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { deals, categories } from '@/data/mockData';
import { Link, useNavigate } from 'react-router-dom';

type SearchAutoSuggestProps = {
  onSearch: (query: string, city?: string) => void;
  initialQuery?: string;
  selectedCity?: string;
};

const SearchAutoSuggest = ({ onSearch, initialQuery = '', selectedCity }: SearchAutoSuggestProps) => {
  const [query, setQuery] = useState(initialQuery);
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const suggestionsRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const navigate = useNavigate();

  // Generate suggestions based on query
  useEffect(() => {
    if (query.length < 2) {
      setSuggestions([]);
      return;
    }
    
    const queryLower = query.toLowerCase();
    
    // Get suggestions from deal titles
    const dealSuggestions = deals
      .filter(deal => deal.title.toLowerCase().includes(queryLower))
      .map(deal => deal.title)
      .slice(0, 3);
      
    // Get suggestions from categories
    const categorySuggestions = categories
      .filter(cat => cat.name.toLowerCase().includes(queryLower))
      .map(cat => cat.name)
      .slice(0, 2);
      
    // Combine and remove duplicates
    const combinedSuggestions = Array.from(new Set([...dealSuggestions, ...categorySuggestions])).slice(0, 5);
    
    setSuggestions(combinedSuggestions);
  }, [query]);

  // Close suggestions on click outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (suggestionsRef.current && !suggestionsRef.current.contains(event.target as Node) &&
          inputRef.current && !inputRef.current.contains(event.target as Node)) {
        setShowSuggestions(false);
      }
    };
    
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (query.trim()) {
      onSearch(query, selectedCity);
      setShowSuggestions(false);
    }
  };

  const handleSuggestionClick = (suggestion: string) => {
    setQuery(suggestion);
    onSearch(suggestion, selectedCity);
    setShowSuggestions(false);
  };

  return (
    <div className="relative w-full">
      <form onSubmit={handleSearch} className="w-full">
        <div className="relative">
          <Input
            ref={inputRef}
            type="search"
            placeholder="Search deals..."
            className="w-full pl-10 border-primary/30 focus:border-primary ring-primary shadow-sm bg-gray-50"
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              setShowSuggestions(true);
            }}
            onFocus={() => setShowSuggestions(true)}
          />
          <Search className="absolute left-3 top-2.5 h-4 w-4 text-primary" />
        </div>
        
        {/* Suggestions dropdown */}
        {showSuggestions && suggestions.length > 0 && (
          <div 
            ref={suggestionsRef}
            className="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 py-1"
          >
            {suggestions.map((suggestion, index) => (
              <div 
                key={index}
                className="px-4 py-2 hover:bg-gray-50 cursor-pointer flex items-center"
                onClick={() => handleSuggestionClick(suggestion)}
              >
                <Tag className="h-3 w-3 mr-2 text-primary" />
                <span>{suggestion}</span>
              </div>
            ))}
          </div>
        )}
      </form>
    </div>
  );
};

export default SearchAutoSuggest;
