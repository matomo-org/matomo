import * as React from 'react';

const { piwik } = window;

export class MatomoModal extends React.Component {
    constructor(props) {
        super(props);
        this.modalRoot = React.createRef();
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (this.props.showModal && !prevProps.showModal && this.modalRoot.current) {
            piwik.helper.modalConfirm(this.modalRoot.current, {
                yes: () => this.props.onYes && this.props.onYes(),
                no: () => this.props.onNo && this.props.onNo(),
            }, {
                onCloseEnd: () => this.props.onCloseEnd && this.props.onCloseEnd(),
            });
        }
    }

    render() {
        return <div ref={this.modalRoot} style={{display:'none'}}>{this.props.children}</div>;
    }
}
