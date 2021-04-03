import { Application } from './Application';
import { Theme } from './Utility/Theme';
import { Router } from './Router';
import { SingleImage } from './Components/SingleImage';
import { SingleUser } from './Components/SingleUser';
import { Create } from './Components/Create';
import { Preferences } from './Components/Preferences';
import { Register } from './Components/Register';
import { Login } from './Components/Login';
import { List } from './Components/List';
import { ForgotPassword } from './Components/ForgotPassword';
import { Account } from './Components/Account';
import { Verify } from './Components/Verify';
import { Notification } from './UI/Notification';
import { ResetPassword } from './Components/ResetPassword';

// Set Dark theme
Theme.initialize();
Notification.initialize();

// Map URLs to Components
const router = new Router();
router.add('^/login$', Login);
router.add('^/register$', Register);
router.add('^/verify$', Verify);
router.add('^/forgot-password$', ForgotPassword);
router.add('^/reset-password$', ResetPassword);
router.add('^/preferences$', Preferences);
router.add('^/create$', Create);
router.add('^/account$', Account);
router.add('^/user/(\\d+)$', SingleUser);
router.add('^/(\\d+)$', SingleImage);
router.add('^/(list|all|)$', List);

// Application
window.addEventListener('load', async (event) => {
	const app = new Application(router);
	await app.auth.status();
	app.navigate(window.location.pathname, window.location.search);
});
