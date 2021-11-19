import React from "react";
import { useHistory } from "react-router-dom";
import styled from "styled-components";
import { HeartIcon, CommentIcon } from "./Icons";

const Wrapper = styled.div`
  display: flex;
  width: 930px;

  @media screen and (max-width: 1140px) {
    width: 95%;
  }
`;

const Container = styled.div`
	margin-top: 1rem;
	cursor: pointer;
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	grid-gap: 1.5rem;

	img {
		border-radius: 4px;
		box-shadow: 0 1px 0 rgba(0, 0, 0, 0.2);
		width: 300px;
		height: 300px;
		object-fit: cover;
	}

	.container-overlay {
		position: relative;
	}

	.container-overlay:hover .overlay {
		display: block;
	}

	.overlay {
		border-radius: 4px;
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.6);
		width: 300px;
		height: 300px;
		z-index: 4;
		display: none;
	}

	.overlay-content {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100%;
		color: ${(props) => props.theme.white};
		font-weight: 500;
		font-size: 1.1rem;
	}

	svg {
		fill: ${(props) => props.theme.white};
		position: relative;
		top: 4px;
	}

	span {
		display: flex;
		display: block;
		align-items: center;
		padding-right: 0.5rem;
	}

	span:first-child {
		margin-right: 1rem;
	}
  
  

	@media screen and (max-width: 1140px) {
		img, .overlay {
          width: 100%;
          height: 250px;
        }
	}

	@media screen and (max-width: 800px) {
		grid-template-columns: 1fr 1fr;

		img, .overlay {
          height: 240px;
          width: 100%;
        }
	}

	@media screen and (max-width: 600px) {
		grid-gap: 1rem;

		img, .overlay {
          height: 200px;
          width: 100%;
        }
	}

	@media screen and (max-width: 500px) {
		img, .overlay {
			height: 170px;
			width: 100%;
	    }
	}

  @media screen and (max-width: 450px) {
    grid-template-columns: 1fr ;

    img, .overlay {
      height: 200px;
      width: 100%;
    }
  }
`;

const ProfilePreview = ({ posts }) => {
  const history = useHistory();
  return (
    <Wrapper>
      <Container>
        {posts?.map((post) => (
            <div
                key={post._id}
                className="container-overlay"
                onClick={() => history.push(`/p/${post._id}`)}
            >
              <img src={post.files[0]} alt="post" />
              <div className="overlay">
                <div className="overlay-content">
              <span>
                <HeartIcon /> {post.likesCount}
              </span>
                  <span>
                <CommentIcon /> {post.commentsCount}
              </span>
                </div>
              </div>
            </div>
        ))}
      </Container>
    </Wrapper>

  );
};

export default ProfilePreview;
