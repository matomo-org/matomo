import * as React from 'react';

const { $ } = window;

// we can't just pass angular transclude elements to React's children property, because React expects
// React wrapped elements (ie, object created with React.createElement()), and can't manage them.
// This component will take those targets and add them manually. I'm not sure how well this will work
// in the long run, but ideally it would be removed after the migration.
export class TranscludeTarget extends React.Component {
    constructor(props) {
        super(props);
        this.target = React.createRef();
    }

    componentDidMount() {
        $(this.target.current).append(this.props.transclude);
    }

    render() {
        return (
            <div ref={this.target}/>
        );
    }
}
