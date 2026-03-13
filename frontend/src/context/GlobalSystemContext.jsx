import { createContext, useContext, useEffect, useState } from 'react';

const GlobalSystemContext = createContext();

export const GlobalSystemProvider = ({ children }) => {
  const [subscriptionBlocked, setSubscriptionBlocked] = useState(false);
  const [subscriptionWarning, setSubscriptionWarning] = useState(null);
  const [planLimitReached, setPlanLimitReached] = useState(false);

  useEffect(() => {
    const handleSubscriptionBlocked = () => {
      setSubscriptionBlocked(true);
    };

    const handleSubscriptionWarning = (e) => {
      setSubscriptionWarning(e.detail);
    };

    const handlePlanLimitReached = () => {
      setPlanLimitReached(true);
    };

    window.addEventListener('subscription-blocked', handleSubscriptionBlocked);
    window.addEventListener('subscription-warning', handleSubscriptionWarning);
    window.addEventListener('plan-limit-reached', handlePlanLimitReached);

    return () => {
      window.removeEventListener('subscription-blocked', handleSubscriptionBlocked);
      window.removeEventListener('subscription-warning', handleSubscriptionWarning);
      window.removeEventListener('plan-limit-reached', handlePlanLimitReached);
    };
  }, []);

  const resetPlanLimit = () => setPlanLimitReached(false);
  const resetSubscriptionBlocked = () => setSubscriptionBlocked(false);

  return (
    <GlobalSystemContext.Provider
      value={{
        subscriptionBlocked,
        subscriptionWarning,
        planLimitReached,
        resetPlanLimit,
        resetSubscriptionBlocked,
      }}
    >
      {children}
    </GlobalSystemContext.Provider>
  );
};

export const useGlobalSystem = () => useContext(GlobalSystemContext);
