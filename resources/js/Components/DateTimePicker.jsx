import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

/**
 * Date and time picker component
 * 
 * @param {Date|string} selected - The selected date/time
 * @param {Function} onChange - Callback when date/time changes
 * @param {Date} minDate - Minimum selectable date
 * @param {Date} maxDate - Maximum selectable date
 * @param {string} error - Error message to display
 * @param {string} placeholderText - Placeholder text
 * @param {boolean} required - Whether the field is required
 */
export default function DateTimePicker({ 
    selected, 
    onChange, 
    minDate,
    maxDate,
    error, 
    placeholderText = 'Select date and time',
    required = false
}) {
    const dateValue = selected ? (typeof selected === 'string' ? new Date(selected) : selected) : null;

    return (
        <div>
            <DatePicker
                selected={dateValue}
                onChange={onChange}
                showTimeSelect
                timeFormat="HH:mm"
                timeIntervals={15}
                timeCaption="Time"
                dateFormat="MMMM d, yyyy HH:mm"
                minDate={minDate}
                maxDate={maxDate}
                placeholderText={placeholderText}
                required={required}
                className={`w-full border-2 px-3 py-2 focus:outline-none sm:text-sm ${
                    error ? 'border-danger' : 'border-grey-300 focus:border-secondary'
                }`}
                calendarClassName="date-picker-calendar"
                wrapperClassName="w-full"
            />
            {error && (
                <p className="mt-1 text-sm text-danger">{error}</p>
            )}
        </div>
    );
}
