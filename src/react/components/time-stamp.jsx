/**
 * Display date and time
 */
wplv.TimeStamp = React.createClass({

	// Get initial state
	getInitialState: function() {
		return {
			localeSupported: null
		};
	},

	// Default properties
	getDefaultProps: function() {
		return {
			date: ''
		};
	},

	// Property types
	propTypes: {
		date: React.PropTypes.object.isRequired
	},

	// Before mount
	componentWillMount: function() {
		try {
			if (this.state.localeSupported === null) {
		    	new Date().toLocaleDateString('i');
		    	this.setState({localeSupported: true});
			}
	 	} catch (e) {
			this.setState({localeSupported: false});
		}
	},

	// Render component
	render: function() {
		if (this.props.date instanceof Date) {
			var now = new Date();

			if (this.state.localeSupported) {
				var today = now.toLocaleDateString(),
					currentDate = this.props.date.toLocaleDateString(),
					currentTime = this.props.date.toLocaleTimeString();
			} else {
				var today = now.toDateString().replace(/([\w]+) ([\w]+) ([\d]+) ([\d]+)/, "$2 $3 $4"),
					currentDate = this.props.date.toDateString().replace(/([\w]+) ([\w]+) ([\d]+) ([\d]+)/, "$2 $3 $4"),
					currentTime = this.props.date.toTimeString().replace(/.*(\d{2}:\d{2}:\d{2})[ \w+-]+\(([\w]+)\)/, "$1 $2");
			}

			if (today === currentDate) {
				currentDate = 'Today';
			}

			return (
				<div className="wplv-module--timestamp">
					<div className="date">{ currentDate }</div>
					<div className="time">{ currentTime }</div>
				</div>
			);
		}

		return (
			<div className="wplv-module--timestamp"></div>
		);
	}
});