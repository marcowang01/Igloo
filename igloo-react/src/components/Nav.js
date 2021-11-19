import React, { useContext } from "react";
import { Link } from "react-router-dom";
import styled from "styled-components";
import NewPost from "./NewPost";
import Search from "./Search";
import { UserContext } from "../context/UserContext";
import navlogo from "../assets/navlogo.png";
import { HomeIcon, CloseIcon, SearchIcon } from "./Icons";
import {toast} from "react-toastify";

const NavWrapper = styled.div`
  position: fixed;
  top: 0;
  width: 100%;
  background-color: ${(props) => props.theme.white};
  border-bottom: 1px solid ${(props) => props.theme.borderColor};
  padding: 2rem 0;
  z-index: 10;

  .nav-logo {
    position: relative;
    top: 6px;
  }

  nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 auto;
    padding-left: 2rem;
    padding-right: 2rem;
    width: 100%;
  }

  ul {
    display: flex;
    position: relative;
    top: 3px;
    list-style-type: none;
  }

  li {
    margin-left: 1rem;
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

const Nav = () => {
  const { user, setUser } = useContext(UserContext);

  const handleLogout = () => {
    setUser(null);
    localStorage.removeItem("user");
    localStorage.removeItem("token");
    toast.success("You are logged out");
  };

  return (
    <NavWrapper>
      <nav>
        <ul>
          <li>
            <SearchIcon />
            <Search />
          </li>
        </ul>
        <ul>
          <Link to="/home">
            <img className="nav-logo" src={navlogo} alt="logo" />
          </Link>
        </ul>
        <ul>
          <li>
            <Link to={`/${user.username}`}>
              {user.username}
            </Link>
          </li>
          <li>
            <CloseIcon onClick={handleLogout} />
            {/*<Link to="/home">*/}
              {/*<img*/}
              {/*    onClick={handleLogout}*/}
              {/*    style={{*/}
              {/*      width: "24px",*/}
              {/*      height: "24px",*/}
              {/*      objectFit: "cover",*/}
              {/*      borderRadius: "12px",*/}
              {/*    }}*/}
              {/*    src={user.avatar}*/}
              {/*    alt="avatar"*/}
              {/*/>*/}
            {/*</Link>*/}
          </li>
        </ul>
      </nav>
    </NavWrapper>
  );
};

export default Nav;
