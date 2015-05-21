/**
 * Display a group entry
 */
var GroupEntry = React.createClass({

	// Get initial state
	getInitialState: function() {
		return {
			showDetails: false
		};
	},
	
	// Get properties
	getDefaultProps: function() {
		return {
			group: {
				date: '',
				time: '',
				timezone: '',
				message: '',
				entries: []	
			}
		};
	},

	// Property types
	propTypes: {
		group: React.PropTypes.object
	},

	toggleDetails: function(e) {
		e.preventDefault();

		this.setState({showDetails: !this.state.showDetails});
	},

	render: function() {
		var group = this.props.group;
		var when = new Date(group.date+' '+group.time+' '+group.timezone);
		var groupDetails = '';

		if (this.state.showDetails) {
			var groupEntryDetails = [];
			
			for (var key in group.entries) {
				var entry = group.entries[key];
				var entryWhen = new Date(entry.date + ' ' + entry.time + ' ' + entry.timezone);

				groupEntryDetails.push((
					<div className="when">
						<div className="date">{ entryWhen.toLocaleDateString() }</div>
						<div className="time">{ entryWhen.toLocaleTimeString() }</div>
					</div>
				));
			}
			
			groupDetails = (
				<div className="group-entry-details active">
					<a href="#" className="hide-group-details" onClick={ this.toggleDetails }>Hide details</a>
					
					<p>Date and time errors occured:</p>
					{ groupEntryDetails }
				</div>
			);
		} else {
			groupDetails = (
				<div className="group-entry-details">
					<a href="#" className="show-group-details" onClick={ this.toggleDetails }>More details</a>
				</div>
			);
		}

		return (
			<div className="group-entry">
				<div className="when">
					<span className="date">{ when.toLocaleDateString() }</span>
					<span className="time">{ when.toLocaleTimeString() }</span>
				</div>
				<div className="message">
					{ group.message }
					{ groupDetails }
				</div>
			</div>
		);
	}
});