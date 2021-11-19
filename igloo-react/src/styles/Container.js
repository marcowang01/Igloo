import styled from "styled-components";

const Container = styled.div`
  width: 930px;
  margin: 8.5rem auto;
  margin-bottom: 4rem;

  @media screen and (max-width: 1260px) {
    width: 99%;
    padding-left: 10rem;
  }

  @media screen and (max-width: 530px) {
    font-size: 0.9rem;

  }
`;

export default Container;
