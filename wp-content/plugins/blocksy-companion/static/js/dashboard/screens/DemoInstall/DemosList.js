import {
	createElement,
	Component,
	useEffect,
	useState,
	createContext,
	useContext,
	Fragment,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import { DemosContext } from '../DemoInstall'
import DashboardContext from '../../DashboardContext'

import useProExtensionInFree from '../../helpers/useProExtensionInFree'

import SingleDemo from './SingleDemo'

const DemosList = () => {
	const { demos_list } = useContext(DemosContext)

	return (
		<ul>
			{demos_list
				.filter(
					(v, i) =>
						demos_list.map(({ name }) => name).indexOf(v.name) === i
				)
				.map((demo) => (
					<SingleDemo key={demo.name} demo={demo} />
				))}
		</ul>
	)
}

export default DemosList
