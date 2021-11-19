import React from "react";
import { BrowserRouter as Router, Route, Switch } from "react-router-dom";

// pages, components, styles
import Nav from "./components/Nav";
import SideNav from "./components/SideNav";
import Container from "./styles/Container";
import Home from "./pages/Home";
import Profile from "./pages/Profile";
import Explore from "./pages/Explore";
import DetailedPost from "./pages/DetailedPost";
import EditProfile from "./pages/EditProfile";

const Routing = () => {
  return (
    <Router>
      <Nav />
      <SideNav />
      <Container>
        <Switch>
          <Route path="/explore" component={Explore} />
          <Route path="/p/:postId" component={DetailedPost} />
          <Route path="/accounts/edit" component={EditProfile} />
          <Route path="/home" component={Home} />
          <Route path="/:username" component={Profile} />
        </Switch>
      </Container>
    </Router>
  );
};

export default Routing;
