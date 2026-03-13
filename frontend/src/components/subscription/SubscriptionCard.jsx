import { useSubscription } from '../../context/SubscriptionContext';
import { format } from 'date-fns';

const SubscriptionCard = () => {
    const { subscription, loading } = useSubscription();

    if (loading) {
        return (
            <div className="bg-white rounded-lg shadow-sm border p-6 animate-pulse">
                <div className="h-4 bg-gray-200 rounded w-1/3 mb-4"></div>
                <div className="h-2 bg-gray-200 rounded w-full"></div>
            </div>
        );
    }

    if (!subscription) {
        return null;
    }

    const { plan, lifecycle, usage } = subscription;
    const progress = Math.min(100, usage.usage_percentage || 0);
    const progressColor = progress > 90 ? 'bg-red-500' : progress > 70 ? 'bg-yellow-500' : 'bg-green-500';

    return (
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {/* Header */}
            <div className="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 flex justify-between items-center border-b border-gray-100">
                <div>
                    <h3 className="text-lg font-bold text-gray-800">{plan.name}</h3>
                    <p className="text-xs text-gray-500">Billed {lifecycle.interval}</p>
                </div>
                <span className={`px-3 py-1 rounded-full text-xs font-medium uppercase tracking-wider
                    ${lifecycle.status === 'active' ? 'bg-green-100 text-green-700' :
                        lifecycle.status === 'past_due' ? 'bg-yellow-100 text-yellow-700' :
                            'bg-red-100 text-red-700'}`}>
                    {lifecycle.status.replace('_', ' ')}
                </span>
            </div>

            {/* Content */}
            <div className="p-6 space-y-6">
                {/* Usage Bar */}
                <div>
                    <div className="flex justify-between text-sm mb-1">
                        <span className="text-gray-600 font-medium">Monthly Appointments</span>
                        <span className="text-gray-900 font-bold">{usage.current_cycle_count} / {plan.max_appointments_monthly === -1 ? '∞' : plan.max_appointments_monthly}</span>
                    </div>
                    <div className="w-full bg-gray-100 rounded-full h-2.5">
                        <div
                            className={`h-2.5 rounded-full transition-all duration-500 ${progressColor}`}
                            style={{ width: `${progress}%` }}
                        ></div>
                    </div>
                    {plan.max_appointments_monthly !== -1 && (
                        <p className="text-xs text-gray-400 mt-1 text-right">{usage.remaining_quota} remaining</p>
                    )}
                </div>

                {/* Dates */}
                <div className="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                    <div>
                        <p className="text-gray-400 text-xs uppercase font-semibold mb-1">Current Cycle</p>
                        <p className="text-gray-700 font-medium">Started: {lifecycle.started_at ? format(new Date(lifecycle.started_at), 'MMM d, yyyy') : 'N/A'}</p>
                    </div>
                    <div className="text-right">
                        <p className="text-gray-400 text-xs uppercase font-semibold mb-1">Renews On</p>
                        <p className="text-gray-900 font-bold text-lg">
                            {lifecycle.renews_at ? format(new Date(lifecycle.renews_at), 'MMM d, yyyy') : 'Never'}
                        </p>
                    </div>
                </div>

                {/* Block Status */}
                {usage.is_blocked && (
                    <div className="bg-red-50 border border-red-100 rounded-lg p-3 flex items-start space-x-3">
                        <div className="text-red-500 text-xl">🚫</div>
                        <div>
                            <p className="text-red-700 font-bold text-sm">Account Restricted</p>
                            <p className="text-red-500 text-xs mt-1">
                                Your account is blocked due to subscription status. You cannot create new records until resolved.
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default SubscriptionCard;
