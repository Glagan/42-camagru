import typescript from 'rollup-plugin-typescript2';

export default {
	input: './resources/index.ts',
	plugins: [typescript()],
	output: {
		file: './public/assets/index.js',
		format: 'iife',
		name: 'bundle',
		sourcemap: 'inline',
	},
};
