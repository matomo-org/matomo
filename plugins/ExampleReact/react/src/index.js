import * as React from 'react';

export class WelcomeComponent extends React.Component {
    render() {
        return <h1>Hello, {this.props.name}</h1>;
    }
}