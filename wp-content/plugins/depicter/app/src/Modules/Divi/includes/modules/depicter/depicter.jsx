// External Dependencies
import React, { Component } from "react";
import "./style.css";
import axios from "axios";
import InnerHTML from "dangerously-set-html-content";

class DepicterModule extends Component {
  static slug = "depicter_module";

  constructor() {
    super();
    this.state = {
      content: "",
      loading: true,
    };
    this.getSliderMarkup = this.getSliderMarkup.bind(this);
  }

  componentDidMount() {
    this.getSliderMarkup();
  }

  componentDidUpdate(prevProps) {
    if (prevProps.document_id !== this.props.document_id) {
      this.getSliderMarkup();
    }
  }

  async getSliderMarkup() {
    if (!this.props.document_id) {
      return;
    }

    this.setState({ content: "", loading: true });

    const resp = await axios({
      url: window.depicter_divi.ajax_url,
      method: "get",
      params: {
        action: "depicter/document/render",
        ID: this.props.document_id,
        addImportant: true,
      },
      headers: {
        "X-DEPICTER-CSRF": window.depicter_divi.token,
      },
    });

    this.setState({ content: resp.data, loading: false });

    setTimeout(function() {
      window.Depicter.initAll();
    }, 0);
  }

  render() {
    return this.state.loading ? (
      <h1>Loading</h1>
    ) : (
      <InnerHTML html={this.state.content} />
    );
  }
}

export default DepicterModule;