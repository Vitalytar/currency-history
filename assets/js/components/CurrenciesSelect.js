import React, { Component } from 'react';
import axios from 'axios';

export class CurrenciesSelect extends Component {
    constructor() {
        super();
        this.state = { currenciesData: {}, loading: true };
    }

    componentDidMount() {
        this.fetchCurrenciesData();
    }

    fetchCurrenciesData() {
        axios.get('http://localhost:8000/api/currencies').then(currenciesData => {
            this.setState({ currenciesData, loading: false });
        });
    }

    render() {
        console.log('Debugging data / renderCurrenciesSelect / state', this.state);

        return (
            <div>
                <ul>
                    { /* TODO: Links to fetch */ }
                    <li>Euro</li>
                    <li>USD</li>
                    <li>GBP</li>
                    <li>AUD</li>
                </ul>
            </div>
        );
    }
}
