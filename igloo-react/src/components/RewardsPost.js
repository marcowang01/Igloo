import React, { useState } from "react";
import styled from "styled-components";
import { useHistory } from "react-router-dom";
import LikePost from "./LikePost";
import SavePost from "./SavePost";
import Comment from "./Comment";
import DeletePost from "./DeletePost";
import Modal from "./Modal";
import useInput from "../hooks/useInput";
import Avatar from "../styles/Avatar";
import { client } from "../utils";
import { timeSince } from "../utils";
import { MoreIcon, CommentIcon, InboxIcon } from "./Icons";
import {toast} from "react-toastify";
import Button from "../styles/Button";

const ModalContentWrapper = styled.div`
  width: 300px;
  display: flex;
  flex-direction: column;
  text-align: center;

  span:last-child {
    border: none;
  }

  span {
    display: block;
    padding: 1rem 0;
    border-bottom: 1px solid ${(props) => props.theme.borderColor};
    cursor: pointer;
  }
`;

export const ModalContent = ({ hideGotoPost, postId, closeModal }) => {
    const history = useHistory();

    const handleGoToPost = () => {
        closeModal();
        history.push(`/p/${postId}`);
    };

    return (
        <ModalContentWrapper>
      <span className="danger" onClick={closeModal}>
        Cancel
      </span>
            <DeletePost postId={postId} closeModal={closeModal} goToHome={true} />
            {!hideGotoPost && <span onClick={handleGoToPost}>Go to Post</span>}
        </ModalContentWrapper>
    );
};

export const PostWrapper = styled.div`
  width: 800px;
  background: ${(props) => props.theme.white};
  border: 1px solid ${(props) => props.theme.white};
  margin-bottom: 1.5rem;

  .post-header-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .post-header {
    display: flex;
    align-items: center;
    padding: 1rem;
  }

  .post-header h3 {
    cursor: pointer;
  }

  .post-img {
    width: 500px;
    height: 100%;
  }

  .post-actions {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    padding-bottom: 0.2rem;
  }

  .post-actions svg:last-child {
    margin-left: auto;
  }

  svg {
    margin-right: 1rem;
  }

  .likes-caption-comments {
    padding: 1rem;
    padding-top: 0.3rem;
  }

  .username {
    padding-right: 0.3rem;
  }

  ul {
    display: flex;
    justify-content: space-between;
    position: relative;
    top: 3px;
    list-style-type: none;
    width: 100%;
  }

  li {
    margin-left: 1rem;
    align-items: center;
  }
  
  h1 {
    margin-left: 1rem;
  }
  
  span {
    width: 300px
  }

  @media screen and (max-width: 950px) {
    width: 100%;
    .post-img {
      width: 100%;
    }
  }
  
  @media screen and (max-width: 900px) {
    width: 480px;
    .post-img {
      width: 325px;
    }
  }
`;

const RewardsPost = ({ post }) => {
    const comment = useInput("");
    const history = useHistory();

    const [showModal, setShowModal] = useState(false);
    const closeModal = () => setShowModal(false);

    const [newComments, setNewComments] = useState([]);
    const [likesState, setLikes] = useState(post.likesCount);

    return (
        <PostWrapper>
            <ul>
                <li>
                    <img
                        className="post-img"
                        src={post.files?.length && post.files[0]}
                        alt="post-img"
                    />
                </li>
                <li>
                    <h1>
                            <span className="caption bold">
                                {post.caption}
                            </span>
                    </h1>
                    <ul>
                        <li>
                            <p>owner</p>
                            <p>creator</p>
                            <p>upload time</p>
                            <p>block status</p>
                            <p>rewards</p>
                        </li>
                        <li>
                            <p>:</p>
                            <p>:</p>
                            <p>:</p>
                            <p>:</p>
                            <p>:</p>
                        </li>
                        <li>
                            <p
                                className="pointer"
                                onClick={() => history.push(`/${post.user?.username}`)}
                            >
                                <span className="secondary">
                                    {post.user?.username}
                                </span>
                            </p>
                            <p
                                className="pointer"
                                onClick={() => history.push(`/${post.creator?.username}`)}
                            >
                                <span className="secondary">
                                   {post.creator?.username}
                                </span>
                            </p>
                            <p>
                                <span className="secondary">
                                    {post?.createdAt}
                                </span>
                            </p>
                            <p>
                                <span className="secondary">
                                    {post?.blockStatus}
                                </span>
                            </p>
                            <p>
                                <span className="secondary">
                                    {post?.rewards} IGLOO
                                </span>
                            </p>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <Button
                                secondary
                                onClick={() => toast.success("withdraw successful")}
                            >
                                withdraw
                            </Button>
                        </li>
                        <li>
                            <Button
                                secondary
                                onClick={() => toast.success("collect successful")}
                            >
                                collect
                            </Button>
                        </li>
                        <li>
                            <Button
                                secondary
                                onClick={() => toast.success("mint successful")}
                            >
                                mint
                            </Button>
                        </li>
                    </ul>
                </li>
            </ul>
        </PostWrapper>
    );
};

export default RewardsPost;
