import { useSubscription } from '../../context/SubscriptionContext';

const SubscriptionBanner = () => {
    const { subscription } = useSubscription();

    if (!subscription || subscription.lifecycle.status === 'active') {
        return null;
    }

    const { status, grace_ends_at } = subscription.lifecycle;

    // Warning Banner (Past Due)
    if (status === 'past_due') {
        return (
            <div className="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p className="font-bold">Subscription Alert</p>
                <p>Your subscription is past due. Grace period active until {grace_ends_at}. Please renew immediately.</p>
            </div>
        );
    }

    // Blocking Banner (Expired/Cancelled)
    if (['expired', 'cancelled'].includes(status)) {
        return (
            <div className="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p className="font-bold">Access Restricted</p>
                <p>Your subscription is {status.toUpperCase()}. Account functionality is limited.</p>
            </div>
        );
    }

    return null;
};

export default SubscriptionBanner;
