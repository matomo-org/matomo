import * as React from 'react';

class FocusAnywhereButHereComponent extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isMouseDown: false,
            hasScrolled: false,
        };

        this.onEscapeHandler = this.onEscapeHandler.bind(this);
        this.onMouseDown = this.onMouseDown.bind(this);
        this.onClickOutsideElement = this.onClickOutsideElement.bind(this);
        this.onScroll = this.onScroll.bind(this);
    }

    onClickOutsideElement(event) {
        const hadUsedScrollbar = this.state.isMouseDown && this.state.hasScrolled;

        this.setState({
            isMouseDown: false,
            hasScrolled: false,
        });

        if (hadUsedScrollbar) {
            return;
        }

        if (this.props.element.current.contains(event.target).length === 0) {
            this.props.onLoseFocus && this.props.onLoseFocus();
        }
    }

    onScroll() {
        this.setState({ hasScrolled: true });
    }

    onMouseDown() {
        this.setState({
            isMouseDown: true,
            hasScrolled: false,
        });
    }

    onEscapeHandler(event) {
        if (event.which === 27) {
            this.setState({
                isMouseDown: false,
                hasScrolled: false,
            });

            this.props.onLoseFocus && this.props.onLoseFocus();
        }
    }

    componentDidMount() {
        document.addEventListener('keyup', this.onEscapeHandler);
        document.addEventListener('mousedown', this.onMouseDown);
        document.addEventListener('mouseup', this.onClickOutsideElement);
        document.addEventListener('scroll', this.onScroll);
    }

    componentWillUnmount() {
        document.removeEventListener('keyup', this.onEscapeHandler);
        document.removeEventListener('mousedown', this.onMouseDown);
        document.removeEventListener('mouseup', this.onClickOutsideElement);
        document.removeEventListener('scroll', this.onScroll);
    }

    render() {
        return null;
    }
}


export default React.forwardRef((props, ref) => <FocusAnywhereButHereComponent
    element={ref} {...props}
/>);
