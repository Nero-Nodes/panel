import React from 'react';
import styled from 'styled-components/macro';
import { breakpoint } from '@/theme';
import FlashMessageRender from '@/components/FlashMessageRender';
import tw from 'twin.macro';

const Wrapper = styled.div`
    ${breakpoint('sm')`
        ${tw`w-4/5 mx-auto`}
    `};
    ${breakpoint('md')`
        ${tw`p-10`}
    `};
    ${breakpoint('lg')`
        ${tw`w-3/5`}
    `};
    ${breakpoint('xl')`
        ${tw`w-full`}
        max-width: 700px;
    `};
`;

const Inner = ({ children }: { children: React.ReactNode }) => (
    <div css={tw`w-full bg-neutral-900 shadow-lg rounded-lg p-6 mx-1`}>
        {children}
    </div>
);

const WelcomeFormContainer = ({ children }: { children: React.ReactNode }) => {
    return (
        <div>
            <Wrapper>
                <h2 css={tw`text-3xl text-center text-neutral-100 font-medium py-4`}>
                    Welcome to Nero!
                </h2>
                <FlashMessageRender css={tw`mb-2 px-1`}/>
                <Inner>
                    {children}
                </Inner>
                <p css={tw`text-center text-neutral-500 text-xs mt-4`}>
                &copy; {(new Date()).getFullYear()}&nbsp;Jexactyl, built on <a href="https://pterodactyl.io">Pterodactyl</a>.
                </p>
            </Wrapper>
        </div>
    );
};

export default WelcomeFormContainer;
