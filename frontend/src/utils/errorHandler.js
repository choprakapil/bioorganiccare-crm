import toast from 'react-hot-toast';

export function handleApiError(error, fallbackMessage = 'Unexpected error occurred') {
  if (!error) {
    toast.error(fallbackMessage);
    return;
  }

  // Network error
  if (!error.response) {
    toast.error('Network error. Please check your connection.');
    return;
  }

  const { status, data } = error.response;

  // 401 – Unauthorized
  if (status === 401) {
    toast.error('Session expired. Please login again.');
    return;
  }

  // 402 – Subscription required
  if (status === 402) {
    toast.error(data?.message || 'Subscription required.');
    return;
  }

  // 403 – Forbidden
  if (status === 403) {
    toast.error(data?.message || 'Access denied.');
    return;
  }

  // 422 – Validation errors
  if (status === 422 && data?.errors) {
    const firstError = Object.values(data.errors)[0];
    if (Array.isArray(firstError)) {
      toast.error(firstError[0]);
    } else {
      toast.error(firstError);
    }
    return;
  }

  // Default fallback
  toast.error(data?.message || fallbackMessage);
}
