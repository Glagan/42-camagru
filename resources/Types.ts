interface User {
	id: number;
	username: string;
	email: string;
	verified: boolean;
	receiveComments: boolean;
}

interface Comment {
	id: number;
	sender: string;
	at: string;
	message: string;
}

interface ImageModel {
	id: number;
	user: number;
	at: string;
	likes: number;
	comments: Comment[];
}
