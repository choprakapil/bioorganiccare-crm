import { useGlobalSystem } from '../../context/GlobalSystemContext';

export default function SubscriptionOverlay() {
  const { subscriptionBlocked, resetSubscriptionBlocked } = useGlobalSystem();

  if (!subscriptionBlocked) return null;

  return (
    <div className="fixed inset-0 z-[9999] bg-black/70 flex items-center justify-center">
      <div className="bg-white rounded-2xl p-10 max-w-md w-full text-center shadow-2xl">
        <h2 className="text-2xl font-black text-slate-800 mb-4">
          Subscription Inactive
        </h2>
        <p className="text-slate-600 mb-6">
          Your clinic subscription is currently inactive. Please contact your administrator or upgrade your plan to continue using write features.
        </p>
        <button
          onClick={resetSubscriptionBlocked}
          className="px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-all"
        >
          Dismiss
        </button>
      </div>
    </div>
  );
}
