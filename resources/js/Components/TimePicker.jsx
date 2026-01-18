import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

/**
 * Time-only picker component
 * 
 * @param {Date|string} selected - The selected time
 * @param {Function} onChange - Callback when time changes
 * @param {Date} minTime - Minimum selectable time
 * @param {Date} maxTime - Maximum selectable time
 * @param {string} error - Error message to display
 * @param {string} placeholderText - Placeholder text
 * @param {boolean} required - Whether the field is required
 * @param {boolean} useUTC - If true, interprets and displays times in UTC (default: false)
 */
export default function TimePicker({ 
    selected, 
    onChange, 
    minTime,
    maxTime,
    error, 
    placeholderText = 'Select time',
    required = false,
    useUTC = false
}) {
    let dateValue = null;
    let minTimeValue = minTime;
    let maxTimeValue = maxTime;
    
    if (selected) {
        const inputDate = typeof selected === 'string' ? new Date(selected) : selected;
        
        if (useUTC) {
            // For UTC mode: extract UTC hours/minutes and create a local Date with those values
            // This way, when DatePicker displays it in local time, it shows the UTC time
            const utcHours = inputDate.getUTCHours();
            const utcMinutes = inputDate.getUTCMinutes();
            dateValue = new Date();
            dateValue.setHours(utcHours, utcMinutes, 0, 0);
        } else {
            dateValue = inputDate;
        }
    }
    
    // Convert minTime and maxTime to UTC display mode if needed
    if (useUTC) {
        if (minTime) {
            const minDate = typeof minTime === 'string' ? new Date(minTime) : minTime;
            const utcHours = minDate.getUTCHours();
            const utcMinutes = minDate.getUTCMinutes();
            minTimeValue = new Date();
            minTimeValue.setHours(utcHours, utcMinutes, 0, 0);
        }
        
        if (maxTime) {
            const maxDate = typeof maxTime === 'string' ? new Date(maxTime) : maxTime;
            const utcHours = maxDate.getUTCHours();
            const utcMinutes = maxDate.getUTCMinutes();
            maxTimeValue = new Date();
            maxTimeValue.setHours(utcHours, utcMinutes, 0, 0);
        }
    }

    const handleChange = (date) => {
        if (!date) {
            onChange(null);
            return;
        }
        
        if (useUTC) {
            // Get the hours and minutes from the picker (local time display showing UTC values)
            const hours = date.getHours();
            const minutes = date.getMinutes();
            
            // Create a Date with these as UTC hours/minutes
            const utcDate = new Date();
            utcDate.setUTCHours(hours, minutes, 0, 0);
            onChange(utcDate);
        } else {
            onChange(date);
        }
    };

    return (
        <div>
            <DatePicker
                selected={dateValue}
                onChange={handleChange}
                showTimeSelect
                showTimeSelectOnly
                timeIntervals={15}
                timeCaption={useUTC ? "Time (UTC)" : "Time"}
                dateFormat="HH:mm"
                minTime={minTimeValue}
                maxTime={maxTimeValue}
                placeholderText={placeholderText}
                required={required}
                className={`w-full border-2 px-3 py-2 focus:outline-none sm:text-sm ${
                    error ? 'border-danger' : 'border-grey-300 focus:border-secondary'
                }`}
                calendarClassName="date-picker-calendar"
                wrapperClassName="w-full"
            />
            {useUTC && (
                <p className="mt-1 text-xs text-gray-500">Times are in UTC (Zulu time)</p>
            )}
            {error && (
                <p className="mt-1 text-sm text-danger">{error}</p>
            )}
        </div>
    );
}
