import React, { Component } from 'react';
import axios from 'axios';
import '../../styles/misc.css';

export class CurrenciesSelect extends Component {
    constructor() {
        super();
        this.state = {
            currenciesData: {},
            currencyCode: '',
            sortedColumn: null,
            sortDirection: 'asc',
            currentPage: 0,
            pageSize: 10
        };
        this.fetchCurrenciesData = this.fetchCurrenciesData.bind(this);
    }

    handleSort = column => {
        const { sortedColumn, sortDirection, currenciesData } = this.state;
        let newSortDirection = 'asc';

        if (sortedColumn === column && sortDirection === 'asc') {
            newSortDirection = 'desc';
        }

        const sortedData = currenciesData.sort((a, b) => {
            if (newSortDirection === 'asc') {
                return a[column] > b[column] ? 1 : -1;
            } else {
                return a[column] < b[column] ? 1 : -1;
            }
        });

        this.setState({
            currenciesData: sortedData,
            sortedColumn: column,
            sortDirection: newSortDirection
        });
    };

    handlePreviousPage = () => {
        this.setState(prevState => ({ currentPage: prevState.currentPage - 1 }));
    };

    handleNextPage = () => {
        this.setState(prevState => ({ currentPage: prevState.currentPage + 1 }));
    };

    fetchCurrenciesData(currencyCode) {
        axios.get(`http://localhost:8000/api/currency/${currencyCode}`).then(currenciesData => {
            const { data } = currenciesData;
            this.setState({ currenciesData: data, currencyCode });
        });
    }

    renderCurrencyTable() {
        const {
            currenciesData,
            currencyCode,
            sortedColumn,
            sortDirection,
            currentPage,
            pageSize
        } = this.state;

        if (!Object.keys(currenciesData).length) {
            return null;
        }

        const startIndex = currentPage * pageSize;
        const endIndex = startIndex + pageSize;
        const paginatedData = currenciesData.slice(startIndex, endIndex);
        const mostRecentCurrency = currenciesData.length > 0 ? currenciesData.reduce((prev, current) => (prev.fetchedAt.timestamp > current.fetchedAt.timestamp) ? prev : current) : null;
        const mostRecentFetch = new Date(mostRecentCurrency.fetchedAt.timestamp * 1000).toLocaleString('en-GB');

        return (
            <div className="CurrencyTable">
                Last updated: { mostRecentFetch }
                <table>
                    <thead>
                        <tr>
                            <th onClick={ () => this.handleSort('fetchedAt') }>
                                Date { sortedColumn === 'fetchedAt' && (sortDirection === 'asc' ? '▲' : '▼') }
                            </th>
                            <th onClick={ () => this.handleSort('rate') }>
                                EUR to { currencyCode } { sortedColumn === 'rate' && (sortDirection === 'asc' ? '▲' : '▼') }
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    { paginatedData.map((currency, index) => (
                        <tr key={ index }>
                            <td>{ new Date(currency.fetchedAt.timestamp * 1000).toLocaleString('en-GB') }</td>
                            <td>{ currency.rate }</td>
                        </tr>
                    )) }
                    </tbody>
                </table>
                <div className="pagination">
                    <button onClick={ this.handlePreviousPage } disabled={ currentPage === 0 }>
                        Previous Page
                    </button>
                    <span>
                        Page { currentPage + 1 } of { Math.ceil(currenciesData.length / pageSize) }
                    </span>
                    <button onClick={ this.handleNextPage } disabled={ endIndex >= currenciesData.length }>
                        Next Page
                    </button>
                </div>
                { this.renderMiscData() }
            </div>
        );
    }

    renderMiscData() {
        const { currenciesData } = this.state;

        const averageRate = currenciesData.length > 0
            ? currenciesData.reduce((sum, currency) => sum + parseFloat(currency.rate), 0) / currenciesData.length : 0;

        return (
            <div className="MiscData">
                <div>Minimum: { Math.min(...currenciesData.map(obj => obj.rate)) }</div>
                <div>Maximum: { Math.max(...currenciesData.map(obj => obj.rate)) }</div>
                <div>Average: { averageRate.toFixed(6) }</div>
            </div>
        );
    }

    render() {
        return (
            <>
                <div>
                    <ul>
                        <li onClick={ () => this.fetchCurrenciesData('USD') }>USD</li>
                        <li onClick={ () => this.fetchCurrenciesData('GBP') }>GBP</li>
                        <li onClick={ () => this.fetchCurrenciesData('AUD') }>AUD</li>
                    </ul>
                </div>
                { this.renderCurrencyTable() }
            </>
        );
    }
}
