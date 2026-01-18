import { useState } from 'react';
import Button from './Button';

/**
 * Component for managing a list of airport ICAO codes
 * 
 * @param {Array} value - Array of airport ICAO codes
 * @param {Function} onChange - Callback when airports change
 * @param {string} error - Error message to display
 */
export default function AirportSelector({ value = [], onChange, error }) {
    const [inputValue, setInputValue] = useState('');
    const [inputError, setInputError] = useState('');

    const handleAdd = () => {
        const icao = inputValue.trim().toUpperCase();
        
        // Validate ICAO code (3-4 characters, alphanumeric)
        if (!icao) {
            setInputError('Please enter an airport code');
            return;
        }
        
        if (icao.length < 3 || icao.length > 4) {
            setInputError('Airport codes must be 3-4 characters');
            return;
        }
        
        if (!/^[A-Z0-9]+$/.test(icao)) {
            setInputError('Airport codes can only contain letters and numbers');
            return;
        }
        
        if (value.includes(icao)) {
            setInputError('This airport is already added');
            return;
        }
        
        // Add to list
        onChange([...value, icao]);
        setInputValue('');
        setInputError('');
    };

    const handleRemove = (icao) => {
        onChange(value.filter(a => a !== icao));
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleAdd();
        }
    };

    return (
        <div>
            <div className="flex gap-2 mb-3">
                <input
                    type="text"
                    value={inputValue}
                    onChange={(e) => {
                        setInputValue(e.target.value);
                        setInputError('');
                    }}
                    onKeyPress={handleKeyPress}
                    placeholder="e.g., EKCH, ESSA, ENGM"
                    maxLength={4}
                    className="flex-1 border-2 border-grey-300 px-3 py-2 focus:border-secondary focus:outline-none sm:text-sm"
                />
                <Button type="button" variant="secondary" onClick={handleAdd}>
                    Add
                </Button>
            </div>
            
            {inputError && (
                <p className="text-sm text-danger mb-2">{inputError}</p>
            )}
            
            {error && (
                <p className="text-sm text-danger mb-2">{error}</p>
            )}
            
            {value.length > 0 && (
                <div className="flex flex-wrap gap-2">
                    {value.map((airport) => (
                        <span
                            key={airport}
                            className="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium bg-secondary text-white border-2 border-secondary"
                        >
                            <span className="font-mono">{airport}</span>
                            <button
                                type="button"
                                onClick={() => handleRemove(airport)}
                                className="text-white hover:text-snow focus:outline-none ml-1"
                                aria-label={`Remove ${airport}`}
                            >
                                ×
                            </button>
                        </span>
                    ))}
                </div>
            )}
            
            {value.length === 0 && (
                <p className="text-sm text-grey-500 italic">No airports added yet</p>
            )}
        </div>
    );
}
