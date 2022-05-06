import React from 'react';
import PageContentBlock from '@/components/elements/PageContentBlock';
import tw from 'twin.macro';
import ServerErrorSvg from '@/assets/images/server_error.svg';

const ErrorContainer = () => {
    return (
        <>
            <PageContentBlock>
                <div css={tw`flex justify-center`}>
                    <div css={tw`w-full sm:w-3/4 md:w-1/2 p-12 md:p-20 bg-neutral-100 rounded-lg shadow-lg text-center relative`}>
                        <img src={ServerErrorSvg} css={tw`w-2/3 h-auto select-none mx-auto`} />
                        <h2 css={tw`mt-10 text-neutral-900 font-bold text-4xl`}>Account Terminated</h2>
                        <p css={tw`text-sm text-neutral-700 mt-2`}>
                            Your Nero account has been terminated due to our authentication
                            checks determining that you have multiple accounts signed into the
                            Panel on the same network.
                        </p>
                        <p css={tw`text-sm text-neutral-700 mt-2`}>
                            To resolve this, please <a href={'https://neronodes.net/discord'}>contact support.</a>
                        </p>
                    </div>
                </div>
            </PageContentBlock>
        </>
    );
};

export default ErrorContainer;
