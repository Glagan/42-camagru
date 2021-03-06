interface PublicUser {
	id: number;
	username: string;
	verified: boolean;
}

interface User extends PublicUser {
	email: string;
	receiveComments: boolean;
}

interface ImageComment {
	id: number;
	user: PublicUser;
	at: string;
	message: string;
}

interface ImageModel {
	id: number;
	user: number;
	name: string;
	animated: boolean;
	at: string;
}

type DecorationPosition =
	| 'top-left'
	| 'top-right'
	| 'top-center'
	| 'center-center'
	| 'center-left'
	| 'center-right'
	| 'bottom-left'
	| 'bottom-right'
	| 'bottom-center';
interface Decoration {
	id: number;
	name: string;
	animated: boolean;
	position: DecorationPosition;
}
