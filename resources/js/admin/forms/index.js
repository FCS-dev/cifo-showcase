import loginForm from './loginForm';
import forgotPasswordForm from './forgotPasswordForm';
import confirmPasswordForm from './confirmPasswordForm';

export default function registerForms(Alpine) {
    Alpine.data('loginForm', loginForm);
    Alpine.data('forgotPasswordForm', forgotPasswordForm);
    Alpine.data('confirmPasswordForm', confirmPasswordForm);
}