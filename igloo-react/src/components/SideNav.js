import React, { useContext } from "react";
import { NavLink } from "react-router-dom";
import styled from "styled-components";
import NewPost from "./NewPost";
import Search from "./Search";
import { UserContext } from "../context/UserContext";
import navlogo from "../assets/navlogo.png";
import { HomeIcon, ExploreIcon, SearchIcon } from "./Icons";

const SideNavWrapper = styled.div`
  position: fixed;
  top: 6rem;
  width: 10%;
  // background-color: ${(props) => props.theme.white};
  // border-bottom: 1px solid ${(props) => props.theme.borderColor};
  // border-right: 1px solid ${(props) => props.theme.borderColor};
  padding: 2rem 0;
  z-index: 10;

  nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 auto;
    width: 930px;
  }

  ul {
    display: block;
    position: relative;
    top: 3px;
    list-style-type: none;
    align-items: center;
  }

  li {
    margin-left: 5rem;
    align-items: center;
  }

  @media screen and (max-width: 970px) {
    nav {
      width: 90%;
    }
  }

  @media screen and (max-width: 670px) {
    input {
      display: none;
    }
  }
`;

const SideNav = () => {
    const { user } = useContext(UserContext);

    return (
        <SideNavWrapper>
            <ul>
                <li>
                    <NavLink
                        to="/home"
                        activeStyle={{fontWeight: "bold",}}
                    >
                        FEED
                    </NavLink>
                </li>
                <li>
                    <NavLink
                        to="/explore"
                        activeStyle={{fontWeight: "bold",}}
                    >
                        EXPLORE
                    </NavLink>
                </li>
                <li>
                    <NavLink
                        to={`/${user.username}`}
                        activeStyle={{fontWeight: "bold",}}
                    >
                        HOME
                    </NavLink>
                </li>
            </ul>
        </SideNavWrapper>
    );
};

export default SideNav;
