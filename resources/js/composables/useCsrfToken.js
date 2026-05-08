export function useCsrfToken() {
  return () => {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    return token ? token.content : null;
  };
}